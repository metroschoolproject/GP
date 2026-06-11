<?php

trait JsonResponseTrait
{
    protected function jsonResponse($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }
}
