<?php

require_once APPROOT . '/libraries/Database.php';

class SupplierKpiService
{
    private $db;
    private $bookingModel;
    private $reviewModel;

    public function __construct($bookingModel, $reviewModel)
    {
        $this->db = new Database();
        $this->bookingModel = $bookingModel;
        $this->reviewModel = $reviewModel;
    }

    /**
     * Full KPI scorecard for a single supplier (detail page).
     */
    public function calculateSupplierKpi(int $supplierId, array $listRow = []): array
    {
        $profile = $this->scoreProfileCompleteness($supplierId, $listRow);
        $reliability = $this->scoreReliability($supplierId, $listRow);
        $satisfaction = $this->scoreCustomerSatisfaction($supplierId, $listRow);
        $responsiveness = $this->scoreResponsiveness($supplierId, $listRow);

        $totalScore = $profile['score'] + $reliability['score']
                    + $satisfaction['score'] + $responsiveness['score'];

        $tier = $this->resolveTier($totalScore);

        return [
            'score' => $totalScore,
            'tier' => $tier['key'],
            'tier_label' => $tier['label'],
            'tier_color' => $tier['color'],
            'dimensions' => [$profile, $reliability, $satisfaction, $responsiveness],
        ];
    }

    /**
     * Batch KPI computation for the suppliers list page.
     * Takes the array from getApplications() and returns [supplierId => kpiData].
     */
    public function calculateKpiForList(array $suppliers): array
    {
        if (empty($suppliers)) {
            return [];
        }

        $ids = array_map(fn($s) => (int)$s['supplier_id'], $suppliers);

        // Batch query 1: documents (cover photo + business license)
        $docsBySupplier = $this->batchFetchDocuments($ids);

        // Batch query 2: category counts
        $catsBySupplier = $this->batchFetchCategoryCounts($ids);

        // Batch query 3: service counts (total + active)
        $servicesBySupplier = $this->batchFetchServiceCounts($ids);

        $results = [];
        foreach ($suppliers as $row) {
            $sid = (int)$row['supplier_id'];

            // Enrich listRow with batch data
            $enriched = $row;
            $enriched['_has_cover'] = !empty($docsBySupplier[$sid]['cover_photo']);
            $enriched['_has_license'] = !empty($docsBySupplier[$sid]['business_license']);
            $enriched['_category_count'] = $catsBySupplier[$sid] ?? 0;
            $enriched['_active_service_count'] = $servicesBySupplier[$sid]['active'] ?? 0;
            $enriched['_total_service_count'] = $servicesBySupplier[$sid]['total'] ?? 0;

            $results[$sid] = $this->calculateSupplierKpi($sid, $enriched);
        }

        return $results;
    }

    // ---------------------------------------------------------------
    // Dimension 1: Profile Completeness (30 points max)
    // ---------------------------------------------------------------
    private function scoreProfileCompleteness(int $supplierId, array $lr): array
    {
        $items = [];
        $total = 0;

        // Shop name + description (5 pts)
        $hasName = !empty(trim((string)($lr['shop_name'] ?? '')));
        $hasDesc = !empty(trim((string)($lr['description'] ?? '')));
        $nameScore = ($hasName && $hasDesc) ? 5 : ($hasName ? 2 : 0);
        $total += $nameScore;
        $items[] = [
            'label' => 'Shop name & description',
            'score' => $nameScore,
            'max' => 5,
            'value' => $nameScore === 5 ? 'Complete' : ($nameScore > 0 ? 'Name only' : 'Missing'),
        ];

        // Cover photo (5 pts)
        $hasCover = !empty($lr['_has_cover']);
        $coverScore = $hasCover ? 5 : 0;
        $total += $coverScore;
        $items[] = [
            'label' => 'Cover photo',
            'score' => $coverScore,
            'max' => 5,
            'value' => $hasCover ? 'Uploaded' : 'Missing',
        ];

        // Business license (3 pts)
        $hasLicense = !empty($lr['_has_license']);
        $licenseScore = $hasLicense ? 3 : 0;
        $total += $licenseScore;
        $items[] = [
            'label' => 'Business license',
            'score' => $licenseScore,
            'max' => 3,
            'value' => $hasLicense ? 'Uploaded' : 'Missing',
        ];

        // Categories assigned (3 pts)
        $catCount = (int)($lr['_category_count'] ?? 0);
        $catScore = $catCount >= 1 ? 3 : 0;
        $total += $catScore;
        $items[] = [
            'label' => 'Categories',
            'score' => $catScore,
            'max' => 3,
            'value' => $catCount > 0 ? $catCount . ' assigned' : 'None',
        ];

        // Active service (5 pts)
        $activeCount = (int)($lr['_active_service_count'] ?? 0);
        $activeScore = $activeCount >= 1 ? 5 : 0;
        $total += $activeScore;
        $items[] = [
            'label' => 'Active service',
            'score' => $activeScore,
            'max' => 5,
            'value' => $activeCount > 0 ? $activeCount . ' active' : 'None',
        ];

        // Services published / readiness (5 pts)
        // Light check: all active services have publish_status='published'
        $totalCount = (int)($lr['_total_service_count'] ?? 0);
        if ($totalCount > 0 && $activeCount > 0) {
            $pubReady = $this->countPublishedServices($supplierId);
            $ratio = $activeCount > 0 ? $pubReady / $activeCount : 0;
            if ($ratio >= 1.0) {
                $pubScore = 5;
                $pubVal = 'All published';
            } elseif ($ratio >= 0.5) {
                $pubScore = 3;
                $pubVal = $pubReady . '/' . $activeCount . ' published';
            } else {
                $pubScore = 1;
                $pubVal = $pubReady . '/' . $activeCount . ' published';
            }
        } else {
            $pubScore = 0;
            $pubVal = 'No services';
        }
        $total += $pubScore;
        $items[] = [
            'label' => 'Services published',
            'score' => $pubScore,
            'max' => 5,
            'value' => $pubVal,
        ];

        // Payment status (4 pts)
        $isPaid = strtolower((string)($lr['payment_status'] ?? '')) === 'paid';
        $payScore = $isPaid ? 4 : 0;
        $total += $payScore;
        $items[] = [
            'label' => 'Platform fee paid',
            'score' => $payScore,
            'max' => 4,
            'value' => $isPaid ? 'Paid' : 'Unpaid',
        ];

        return [
            'name' => 'Profile Completeness',
            'score' => $total,
            'max' => 30,
            'items' => $items,
        ];
    }

    // ---------------------------------------------------------------
    // Dimension 2: Reliability (25 points max)
    // ---------------------------------------------------------------
    private function scoreReliability(int $supplierId, array $lr): array
    {
        $items = [];
        $total = 0;

        // Fetch booking breakdown
        $bk = $this->getBookingBreakdown($supplierId);
        $totalBookings = $bk['total'];
        $completed = $bk['completed'];
        $cancelled = $bk['cancelled'];

        // Completion rate (10 pts)
        if ($totalBookings > 0) {
            $completionRate = $completed / $totalBookings;
            if ($completionRate >= 1.0) {
                $compScore = 10;
            } elseif ($completionRate >= 0.9) {
                $compScore = 8;
            } elseif ($completionRate >= 0.8) {
                $compScore = 5;
            } else {
                $compScore = 2;
            }
            $compVal = round($completionRate * 100) . '%';
        } else {
            $compScore = 5; // no-data baseline
            $compVal = 'No bookings';
        }
        $total += $compScore;
        $items[] = [
            'label' => 'Completion rate',
            'score' => $compScore,
            'max' => 10,
            'value' => $compVal,
        ];

        // Cancellation rate (7 pts, inverse)
        if ($totalBookings > 0) {
            $cancelRate = $cancelled / $totalBookings;
            if ($cancelRate <= 0.0) {
                $canScore = 7;
            } elseif ($cancelRate <= 0.10) {
                $canScore = 5;
            } elseif ($cancelRate <= 0.20) {
                $canScore = 3;
            } else {
                $canScore = 1;
            }
            $canVal = round($cancelRate * 100) . '%';
        } else {
            $canScore = 4; // no-data baseline
            $canVal = 'No bookings';
        }
        $total += $canScore;
        $items[] = [
            'label' => 'Cancellation rate',
            'score' => $canScore,
            'max' => 7,
            'value' => $canVal,
        ];

        // Warning level (8 pts)
        $warnLevel = (int)($lr['warning_level'] ?? 0);
        if ($totalBookings === 0) {
            $warnScore = min(5, 8); // cap at 5 if no bookings
        } else {
            $warnScore = match (true) {
                $warnLevel === 0 => 8,
                $warnLevel === 1 => 4,
                default => 0,
            };
        }
        $warnVal = match (true) {
            $warnLevel === 0 => 'Clean',
            $warnLevel === 1 => 'Warning',
            default => 'Final warning',
        };
        $total += $warnScore;
        $items[] = [
            'label' => 'Warning status',
            'score' => $warnScore,
            'max' => 8,
            'value' => $warnVal,
        ];

        return [
            'name' => 'Reliability',
            'score' => $total,
            'max' => 25,
            'items' => $items,
        ];
    }

    // ---------------------------------------------------------------
    // Dimension 3: Customer Satisfaction (25 points max)
    // ---------------------------------------------------------------
    private function scoreCustomerSatisfaction(int $supplierId, array $lr): array
    {
        $items = [];
        $total = 0;

        $avgRating = (float)($lr['avg_rating'] ?? 0);
        $reviewCount = (int)($lr['review_count'] ?? 0);

        // Average rating (15 pts)
        if ($reviewCount > 0) {
            if ($avgRating >= 4.8) {
                $ratScore = 15;
            } elseif ($avgRating >= 4.5) {
                $ratScore = 12;
            } elseif ($avgRating >= 4.0) {
                $ratScore = 9;
            } elseif ($avgRating >= 3.5) {
                $ratScore = 6;
            } else {
                $ratScore = 3;
            }
            $ratVal = number_format($avgRating, 1) . ' / 5';
        } else {
            $ratScore = 7; // neutral baseline
            $ratVal = 'No reviews';
        }
        $total += $ratScore;
        $items[] = [
            'label' => 'Average rating',
            'score' => $ratScore,
            'max' => 15,
            'value' => $ratVal,
        ];

        // Review volume (5 pts)
        if ($reviewCount >= 10) {
            $volScore = 5;
        } elseif ($reviewCount >= 5) {
            $volScore = 4;
        } elseif ($reviewCount >= 2) {
            $volScore = 3;
        } elseif ($reviewCount >= 1) {
            $volScore = 2;
        } else {
            $volScore = 1;
        }
        $total += $volScore;
        $items[] = [
            'label' => 'Review volume',
            'score' => $volScore,
            'max' => 5,
            'value' => $reviewCount . ' review' . ($reviewCount !== 1 ? 's' : ''),
        ];

        // Rating consistency (5 pts)
        if ($reviewCount >= 2) {
            $dist = $this->getRatingDistribution($supplierId);
            $positive = ($dist[5] ?? 0) + ($dist[4] ?? 0);
            $posRatio = $positive / $reviewCount;
            if ($posRatio >= 0.7) {
                $conScore = 5;
                $conVal = 'Mostly positive';
            } elseif ($posRatio >= 0.4) {
                $conScore = 3;
                $conVal = 'Mixed';
            } else {
                $conScore = 1;
                $conVal = 'Mostly negative';
            }
        } else {
            $conScore = 3; // neutral baseline
            $conVal = $reviewCount === 0 ? 'No reviews' : 'Only 1 review';
        }
        $total += $conScore;
        $items[] = [
            'label' => 'Rating consistency',
            'score' => $conScore,
            'max' => 5,
            'value' => $conVal,
        ];

        return [
            'name' => 'Customer Satisfaction',
            'score' => $total,
            'max' => 25,
            'items' => $items,
        ];
    }

    // ---------------------------------------------------------------
    // Dimension 4: Responsiveness (20 points max)
    // ---------------------------------------------------------------
    private function scoreResponsiveness(int $supplierId, array $lr): array
    {
        $items = [];
        $total = 0;

        $metrics = $this->bookingModel->getSupplierPerformanceMetrics($supplierId);
        $responseRate = (float)($metrics['response_rate'] ?? 0);
        $acceptanceRate = (float)($metrics['acceptance_rate'] ?? 0);
        $avgHours = (float)($metrics['avg_response_hours'] ?? 0);
        $hasBookings = (int)($metrics['total_bookings'] ?? 0) > 0;

        // Response rate (8 pts)
        if ($hasBookings) {
            if ($responseRate >= 100) {
                $rrScore = 8;
            } elseif ($responseRate >= 90) {
                $rrScore = 7;
            } elseif ($responseRate >= 80) {
                $rrScore = 5;
            } else {
                $rrScore = 3;
            }
            $rrVal = $responseRate . '%';
        } else {
            $rrScore = 4; // no-data baseline
            $rrVal = 'No bookings';
        }
        $total += $rrScore;
        $items[] = [
            'label' => 'Response rate',
            'score' => $rrScore,
            'max' => 8,
            'value' => $rrVal,
        ];

        // Acceptance rate (7 pts)
        if ($hasBookings) {
            if ($acceptanceRate >= 100) {
                $arScore = 7;
            } elseif ($acceptanceRate >= 90) {
                $arScore = 6;
            } elseif ($acceptanceRate >= 80) {
                $arScore = 4;
            } else {
                $arScore = 2;
            }
            $arVal = $acceptanceRate . '%';
        } else {
            $arScore = 3; // no-data baseline
            $arVal = 'No bookings';
        }
        $total += $arScore;
        $items[] = [
            'label' => 'Acceptance rate',
            'score' => $arScore,
            'max' => 7,
            'value' => $arVal,
        ];

        // Avg response time (5 pts)
        if ($hasBookings && $avgHours > 0) {
            if ($avgHours < 2) {
                $rtScore = 5;
            } elseif ($avgHours < 6) {
                $rtScore = 4;
            } elseif ($avgHours < 12) {
                $rtScore = 3;
            } elseif ($avgHours < 24) {
                $rtScore = 2;
            } else {
                $rtScore = 1;
            }
            $rtVal = $avgHours < 1
                ? round($avgHours * 60) . ' min'
                : round($avgHours, 1) . ' hrs';
        } else {
            $rtScore = 2; // no-data baseline
            $rtVal = 'No data';
        }
        $total += $rtScore;
        $items[] = [
            'label' => 'Avg response time',
            'score' => $rtScore,
            'max' => 5,
            'value' => $rtVal,
        ];

        return [
            'name' => 'Responsiveness',
            'score' => $total,
            'max' => 20,
            'items' => $items,
        ];
    }

    // ---------------------------------------------------------------
    // Tier resolution
    // ---------------------------------------------------------------
    private function resolveTier(int $score): array
    {
        return match (true) {
            $score >= 90 => ['key' => 'platinum', 'label' => 'Platinum', 'color' => '#6d4c5b'],
            $score >= 75 => ['key' => 'gold', 'label' => 'Gold', 'color' => '#b7792f'],
            $score >= 60 => ['key' => 'silver', 'label' => 'Silver', 'color' => '#78716C'],
            $score >= 40 => ['key' => 'bronze', 'label' => 'Bronze', 'color' => '#92400E'],
            default      => ['key' => 'needs_improvement', 'label' => 'Needs Improvement', 'color' => '#991B1B'],
        };
    }

    // ---------------------------------------------------------------
    // Data helpers
    // ---------------------------------------------------------------

    /**
     * Single query: fetch documents for one supplier.
     */
    private function fetchDocuments(int $supplierId): array
    {
        $this->db->dbquery('SELECT type FROM supplier_documents WHERE supplier_id = :sid AND type IN (\'cover_photo\', \'business_license\')');
        $this->db->dbbind(':sid', $supplierId);
        $rows = $this->db->getmultidata();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['type']] = true;
        }
        return $result;
    }

    /**
     * Single query: count published services for one supplier.
     */
    private function countPublishedServices(int $supplierId): int
    {
        $this->db->dbquery('SELECT COUNT(*) AS cnt FROM services WHERE supplier_id = :sid AND is_active = 1 AND publish_status = \'published\'');
        $this->db->dbbind(':sid', $supplierId);
        $row = $this->db->getsingledata();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Single query: booking breakdown for reliability scoring.
     */
    private function getBookingBreakdown(int $supplierId): array
    {
        $this->db->dbquery('SELECT COUNT(*) AS total, SUM(status = \'completed\') AS completed, SUM(status IN (\'cancelled\', \'rejected\')) AS cancelled FROM booking_suppliers WHERE supplier_id = :sid');
        $this->db->dbbind(':sid', $supplierId);
        $row = $this->db->getsingledata();
        return [
            'total' => (int)($row['total'] ?? 0),
            'completed' => (int)($row['completed'] ?? 0),
            'cancelled' => (int)($row['cancelled'] ?? 0),
        ];
    }

    /**
     * Single query: rating distribution for consistency scoring.
     */
    private function getRatingDistribution(int $supplierId): array
    {
        $this->db->dbquery('SELECT rating, COUNT(*) AS cnt FROM reviews WHERE supplier_id = :sid AND deleted_at IS NULL GROUP BY rating');
        $this->db->dbbind(':sid', $supplierId);
        $rows = $this->db->getmultidata();
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($rows as $row) {
            $dist[(int)$row['rating']] = (int)$row['cnt'];
        }
        return $dist;
    }

    // ---------------------------------------------------------------
    // Batch helpers (list page optimization)
    // ---------------------------------------------------------------

    private function batchFetchDocuments(array $ids): array
    {
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->dbquery("SELECT supplier_id, type FROM supplier_documents WHERE supplier_id IN ($placeholders) AND type IN ('cover_photo', 'business_license')");
        foreach ($ids as $i => $id) {
            $this->db->dbbind($i + 1, $id);
        }
        $rows = $this->db->getmultidata();

        $result = [];
        foreach ($rows as $row) {
            $sid = (int)$row['supplier_id'];
            if (!isset($result[$sid])) {
                $result[$sid] = [];
            }
            $result[$sid][$row['type']] = true;
        }
        return $result;
    }

    private function batchFetchCategoryCounts(array $ids): array
    {
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->dbquery("SELECT supplier_id, COUNT(*) AS cnt FROM supplier_categories WHERE supplier_id IN ($placeholders) GROUP BY supplier_id");
        foreach ($ids as $i => $id) {
            $this->db->dbbind($i + 1, $id);
        }
        $rows = $this->db->getmultidata();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['supplier_id']] = (int)$row['cnt'];
        }
        return $result;
    }

    private function batchFetchServiceCounts(array $ids): array
    {
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->dbquery("SELECT supplier_id, COUNT(*) AS total, SUM(is_active = 1) AS active FROM services WHERE supplier_id IN ($placeholders) GROUP BY supplier_id");
        foreach ($ids as $i => $id) {
            $this->db->dbbind($i + 1, $id);
        }
        $rows = $this->db->getmultidata();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['supplier_id']] = [
                'total' => (int)$row['total'],
                'active' => (int)$row['active'],
            ];
        }
        return $result;
    }
}
