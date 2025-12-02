<?php

class LogoutController
{
    public function index() {
        session_start();
        $_SESSION = [];
        session_destroy();
        header("Location: login");
        exit();
    }
}