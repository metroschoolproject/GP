<?php


require_once 'config/config.php';
require_once 'helpers/Pagination.php';
require_once 'helpers/flashmessage.php';
require_once 'helpers/redirect.php';
require_once 'helpers/curitemid.php';
require_once 'helpers/setcookie.php';

spl_autoload_register(function ($class) {
    require_once ('libraries/' . $class . '.php');
});


?>