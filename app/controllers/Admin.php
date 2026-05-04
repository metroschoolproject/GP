<?php

class Admin extends Controller
{
    public function __construct()
    {
    }   

    public function overview()
    {
        $this->view('admin/dashboard');
    }

}   