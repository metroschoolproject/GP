<?php

class Main extends Controller
{
    public function __construct()
    {
    }   

    public function home()
    {
        $catalogModel = $this->model('CustomerServiceCatalog');

        $this->view('main/index', [
            'serviceCategories' => $catalogModel->getCategories(),
        ]);
    }

    public function service()
    {
        require_once APPROOT . '/controllers/CustomerServices.php';
        $controller = new CustomerServices();
        $controller->service();
    }

}   
