<?php

ini_set('display_errors', 1);

class Core
{



    protected $curcontroller;
    protected $curmethod;
    protected $params = [];
    public function __construct()
    {
        $url = $this->geturl();

        $this->curcontroller = isset($url[0]) ? ucwords($url[0]) : '';

        // for class 

        if (file_exists('../app/controllers/' . $this->curcontroller . '.php')) {
            require_once ('../app/controllers/' . $this->curcontroller . '.php');
            $this->curcontroller = new $this->curcontroller;

            unset($url[0]);
        } else {

            $this->curmethod = 'sidebar';
            echo "no class";
        }




        // for method 
        if (isset($url[1])) {
            if (method_exists($this->curcontroller, $url[1])) {
                $this->curmethod = $url[1];
                unset($url[1]);
            } else {
                $this->curmethod = 'index';
                              echo $url[1];

            }
        } else {
            $this->curmethod = 'index';
            echo $url[1];

        }


        // for parameter 

        $this->params = $url ? array_values($url) : [];

        if (method_exists($this->curcontroller, $this->curmethod)) {

            call_user_func_array([$this->curcontroller, $this->curmethod], $this->params);
        } else {
            echo "Method does not exist: aa " . $this->curmethod;      

        }
    }


    public function geturl()
    {
        // echo rtrim($_GET['url']);  this remove whitespaces and   This function is particularly useful when dealing with user inputs, form submissions, or other situations where you want to clean up or normalize strings.
        $url = isset($_GET['url']) ? rtrim($_GET['url']) : '';
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = explode('/', $url);
        return $url;
    }



}



?>