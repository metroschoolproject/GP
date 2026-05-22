<?php

class Main extends Controller
{
    public function __construct()
    {
    }   

    public function home()
    {
        $this->view('main/index');
    }

}   