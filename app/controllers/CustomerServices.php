<?php

class CustomerServices extends Controller
{
    public function service()
    {
        $catalogModel = $this->model('CustomerServiceCatalog');
        $filters = $this->filtersFromRequest();

        $this->view('main/service', [
            'catalog' => $catalogModel->getServicePageData($filters),
            'filters' => $filters,
        ]);
    }

    public function detail($serviceId = null)
    {
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            redirect('customerServices/service');
        }

        $catalogModel = $this->model('CustomerServiceCatalog');
        $selectedDate = $this->validDate($_GET['date'] ?? '') ?: '';
        $service = $catalogModel->getServiceDetail($serviceId, $selectedDate);

        if (!$service) {
            redirect('customerServices/service');
        }

        $view = strtolower((string)($service['category'] ?? '')) === 'venue'
            ? 'main/venue_detail'
            : 'main/other_service_detail';

        $this->view($view, [
            'service' => $service,
            'selectedDate' => $selectedDate,
        ]);
    }

    private function filtersFromRequest()
    {
        return [
            'search' => trim($_GET['q'] ?? ''),
            'category' => trim($_GET['category'] ?? 'all'),
            'sort' => trim($_GET['sort'] ?? 'featured'),
            'date' => $this->validDate($_GET['date'] ?? '') ?: '',
        ];
    }

    private function validDate($date)
    {
        $date = trim((string)$date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date ? $date : null;
    }
}
