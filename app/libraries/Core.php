<?php

ini_set('display_errors', 1);

class Core
{
    protected $curcontroller = 'Main';
    protected $curmethod = 'home';
    protected $params = [];

    public function __construct()
    {
        $url = $this->geturl();
        $controllerName = !empty($url[0]) ? ucwords($url[0]) : $this->curcontroller;
        // $controllerPath = '../app/controllers/' . $controllerName . '.php';
        $controllerPath = APPROOT . '/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerPath)) {
            http_response_code(404);
            exit('Controller does not exist: ' . $controllerName);
        }

        require_once $controllerPath;
        $this->curcontroller = new $controllerName();
        unset($url[0]);

        if (isset($url[1]) && method_exists($this->curcontroller, $url[1])) {
            $this->curmethod = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];

        if (!method_exists($this->curcontroller, $this->curmethod)) {
            http_response_code(404);
            exit('Method does not exist: ' . $this->curmethod);
        }

        call_user_func_array([$this->curcontroller, $this->curmethod], $this->params);
    }


    public function geturl()
    {
        // echo rtrim($_GET['url']);  this remove whitespaces and   This function is particularly useful when dealing with user inputs, form submissions, or other situations where you want to clean up or normalize strings.
        $url = isset($_GET['url']) ? rtrim($_GET['url']) : '';
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return $url === '' ? [] : explode('/', $url);
    }
}
?>
