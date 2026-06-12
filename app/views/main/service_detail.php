<?php
$service = $service ?? [];
$category = strtolower(trim((string)($service['category'] ?? '')));
$detailView = $category === 'venue' ? 'venue_detail.php' : 'other_service_detail.php';

require __DIR__ . '/' . $detailView;
