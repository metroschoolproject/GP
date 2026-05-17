<?php


class Controller
{

    public function view($view, $data = [])
    {
        if (file_exists("../app/views/" . $view . ".php")) {
            if (!empty($data)) {
                extract($data);
            }
            require "../app/views/" . $view . ".php";
        } else {
            die("View file didn't exit");
        }

    }
    public function model($model)
    {
        if (file_exists("../app/models/" . ucwords($model) . ".php")) {
            require_once "../app/models/" . ucwords($model) . ".php";
            return new $model();
        } else {
            die("Model file didn't exit");
        }
    }



}


?>
