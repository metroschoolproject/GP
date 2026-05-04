<?php
session_start();

function flash($sessionname = "flash_message", $message = "")
{
    // Set flash message in session
    if (!empty($message)) {
        $_SESSION[$sessionname] = [
            'message' => $message
        ];
    } else {
        // Retrieve flash message from session and clear it
        if (isset($_SESSION[$sessionname])) {
            $flash = $_SESSION[$sessionname];
            unset($_SESSION[$sessionname]);
            return $flash;
        }
    }
}



?>