<?php

class Main extends Controller
{
    public function __construct()
    {
    }   

    public function home()
    {
        $catalogModel = $this->model('CustomerServiceCatalog');
        $packageModel = $this->model('PlatformPackage');

        $this->view('main/index', [
            'serviceCategories' => $catalogModel->getCategories(),
            'featuredPackages' => $packageModel->getFeaturedPackages(3),
        ]);
    }

    public function service()
    {
        require_once APPROOT . '/controllers/CustomerServices.php';
        $controller = new CustomerServices();
        $controller->service();
    }

}   
