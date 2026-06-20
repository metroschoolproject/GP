<?php


require_once 'config/config.php';
require_once 'helpers/Pagination.php';
require_once 'helpers/flashmessage.php';
require_once 'helpers/redirect.php';
require_once 'helpers/curitemid.php';
require_once 'helpers/setcookie.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    require_once APPROOT . '/libraries/' . $class . '.php';
});

require_once 'helpers/security.php';
require_once 'helpers/rememberauth.php';
restoreRememberedUserSession();

?>
