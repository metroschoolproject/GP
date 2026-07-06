<?php

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;

class GoogleAnalyticsService
{
    private string $propertyId;
    private string $credentialsPath;

    public function __construct(?string $propertyId = null, ?string $credentialsPath = null)
    {
        $this->propertyId = trim((string)($propertyId ?? (defined('GA4_PROPERTY_ID') ? GA4_PROPERTY_ID : '')));
        $this->credentialsPath = trim((string)($credentialsPath ?? (defined('GOOGLE_APPLICATION_CREDENTIALS_PATH') ? GOOGLE_APPLICATION_CREDENTIALS_PATH : '')));
    }

    public function getDashboardSummary(string $startDate = '30daysAgo', string $endDate = 'today'): array
    {
        if (!$this->isConfigured()) {
            return $this->unavailable('Google Analytics is not configured yet.');
        }

        try {
            $client = $this->client();
            $summary = $this->fetchSummary($client, $startDate, $endDate);
            $topPages = $this->fetchTopPages($client, $startDate, $endDate);

            $client->close();

            return [
                'available' => true,
                'periodLabel' => $this->periodLabel($startDate, $endDate),
                'totalUsers' => $summary['totalUsers'],
                'activeUsers' => $summary['activeUsers'],
                'sessions' => $summary['sessions'],
                'screenPageViews' => $summary['screenPageViews'],
                'topPages' => $topPages,
                'message' => '',
            ];
        } catch (Throwable $e) {
            error_log('Google Analytics dashboard error: ' . $e->getMessage());
            return $this->unavailable('Google Analytics data could not be loaded. Check API access and service account permissions.');
        }
    }

    public function getDashboardSummaryForFilter(string $filter = 'week', ?string $targetDate = null): array
    {
        [$startDate, $endDate, $label] = $this->dateRangeForFilter($filter, $targetDate);
        $summary = $this->getDashboardSummary($startDate, $endDate);
        $summary['periodLabel'] = $label;
        return $summary;
    }

    private function isConfigured(): bool
    {
        return $this->propertyId !== ''
            && ctype_digit($this->propertyId)
            && $this->credentialsPath !== ''
            && is_file($this->credentialsPath)
            && is_readable($this->credentialsPath);
    }

    private function client(): BetaAnalyticsDataClient
    {
        return new BetaAnalyticsDataClient([
            'credentials' => $this->credentialsPath,
            'transport' => 'rest',
        ]);
    }

    private function fetchSummary(BetaAnalyticsDataClient $client, string $startDate, string $endDate): array
    {
        $response = $client->runReport(new RunReportRequest([
            'property' => $this->propertyName(),
            'date_ranges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'metrics' => [
                new Metric(['name' => 'totalUsers']),
                new Metric(['name' => 'activeUsers']),
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'screenPageViews']),
            ],
        ]));

        $row = $response->getRows()[0] ?? null;
        $values = $row ? $row->getMetricValues() : [];

        return [
            'totalUsers' => $this->metricValue($values, 0),
            'activeUsers' => $this->metricValue($values, 1),
            'sessions' => $this->metricValue($values, 2),
            'screenPageViews' => $this->metricValue($values, 3),
        ];
    }

    private function fetchTopPages(BetaAnalyticsDataClient $client, string $startDate, string $endDate): array
    {
        $response = $client->runReport(new RunReportRequest([
            'property' => $this->propertyName(),
            'date_ranges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'dimensions' => [
                new Dimension(['name' => 'pageTitle']),
                new Dimension(['name' => 'pagePath']),
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'totalUsers']),
            ],
            'order_bys' => [
                new OrderBy([
                    'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
                    'desc' => true,
                ]),
            ],
            'limit' => 6,
        ]));

        $pages = [];
        foreach ($response->getRows() as $row) {
            $dimensions = $row->getDimensionValues();
            $metrics = $row->getMetricValues();
            $pages[] = [
                'title' => $this->dimensionValue($dimensions, 0) ?: 'Untitled page',
                'path' => $this->dimensionValue($dimensions, 1) ?: '/',
                'views' => $this->metricValue($metrics, 0),
                'users' => $this->metricValue($metrics, 1),
            ];
        }

        return $pages;
    }

    private function propertyName(): string
    {
        return 'properties/' . $this->propertyId;
    }

    private function metricValue($values, int $index): int
    {
        if (!isset($values[$index])) {
            return 0;
        }
        return (int)round((float)$values[$index]->getValue());
    }

    private function dimensionValue($values, int $index): string
    {
        return isset($values[$index]) ? (string)$values[$index]->getValue() : '';
    }

    private function periodLabel(string $startDate, string $endDate): string
    {
        if ($startDate === '30daysAgo' && $endDate === 'today') {
            return 'Last 30 days';
        }
        return $startDate . ' to ' . $endDate;
    }

    private function dateRangeForFilter(string $filter, ?string $targetDate): array
    {
        $filter = in_array($filter, ['today', 'week', 'month', 'year'], true) ? $filter : 'week';
        $base = $this->parseTargetDate($targetDate);

        switch ($filter) {
            case 'today':
                $date = $base->format('Y-m-d');
                return [$date, $date, 'Today: ' . $base->format('M j, Y')];

            case 'month':
                $start = $base->modify('first day of this month');
                $end = $base->modify('last day of this month');
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), $base->format('F Y')];

            case 'year':
                $start = $base->setDate((int)$base->format('Y'), 1, 1);
                $end = $base->setDate((int)$base->format('Y'), 12, 31);
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), $base->format('Y')];

            case 'week':
            default:
                $start = $base->modify('monday this week');
                $end = $base->modify('sunday this week');
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), $start->format('M j') . ' - ' . $end->format('M j, Y')];
        }
    }

    private function parseTargetDate(?string $targetDate): DateTimeImmutable
    {
        $timezone = new DateTimeZone(date_default_timezone_get() ?: 'Asia/Yangon');
        $targetDate = trim((string)$targetDate);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $targetDate, $timezone);
            if ($date instanceof DateTimeImmutable) {
                return $date;
            }
        }

        return new DateTimeImmutable('today', $timezone);
    }

    private function unavailable(string $message): array
    {
        return [
            'available' => false,
            'periodLabel' => 'Last 30 days',
            'totalUsers' => 0,
            'activeUsers' => 0,
            'sessions' => 0,
            'screenPageViews' => 0,
            'topPages' => [],
            'message' => $message,
        ];
    }
}
