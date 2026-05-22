<?php

function redirect($page)
{
    if (filter_var($page, FILTER_VALIDATE_URL)) {
        header('location:' . $page);
        exit;
    }

    $redirecturl = URLROOT . '/' . ltrim($page, '/');
    header('location:' . $redirecturl);
    exit;
}



?>
