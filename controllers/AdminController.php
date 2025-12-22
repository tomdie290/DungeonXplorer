<?php

class AdminController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id']) || !isset($_SESSION['username']) || $_SESSION['admin'] != 1) {
            header("Location: login");
            exit;
        } else {
            require_once 'view/admin.php';
        }
    }

    public function manageChapters()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /DungeonXplorer/login');
            exit;
        }
        require 'view/adminManageChapters.php';
    }

    public function storeChapter()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /DungeonXplorer/login');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        $q = $db->prepare("INSERT INTO Chapter (title, content) VALUES (:title, :content)");
        $q->execute([
            'title' => $title,
            'content' => $content
        ]);

        $_SESSION['flash'] = "Chapitre ajouté avec succès.";
        header('Location: /DungeonXplorer/admin/manage_chapters');
        exit;
    }
}
