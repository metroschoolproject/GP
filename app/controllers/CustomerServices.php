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
        $service = $catalogModel->getServiceDetail($serviceId);

        if (!$service) {
            redirect('customerServices/service');
        }

        $this->view('main/service_detail', [
            'service' => $service,
        ]);
    }

    private function filtersFromRequest()
    {
        return [
            'search' => trim($_GET['q'] ?? ''),
            'category' => trim($_GET['category'] ?? 'all'),
            'sort' => trim($_GET['sort'] ?? 'featured'),
        ];
    }
}
