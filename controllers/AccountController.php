<?php

class AccountController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            header("Location: home");
        } else {
            require_once 'view/account.php';
        }
    }
}
?>