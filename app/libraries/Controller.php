<?php


class Controller
{

    public function view($view, $data = [])
    {
        $viewPath = APPROOT . "/views/" . $view . ".php";

        if (file_exists($viewPath)) {
            if (!empty($data)) {
                extract($data);
            }
            require $viewPath;
        } else {
            die("View file didn't exit");
        }

    }
    public function model($model)
    {
        $modelPath = APPROOT . "/models/" . ucwords($model) . ".php";

        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        } else {
            die("Model file didn't exit");
        }
    }



}


?>
