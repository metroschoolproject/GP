<?php

class Admin extends Controller
{
    public function __construct()
    {
    }   

    public function dashboard()
    {
        $this->view('admin/dashboard');
    }

}   