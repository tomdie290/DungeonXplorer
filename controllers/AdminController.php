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
            header('Location: /login');
            exit;
        }
        require 'view/adminManageChapters.php';
    }

    public function storeChapter()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $image = $_POST['image_path'] ?? '';

        $q = $db->prepare("INSERT INTO Chapter (title, description, image) VALUES (:title, :content, :image)");
        $q->execute([
            'title' => $title,
            'content' => $content,
            'image' => $image
        ]);

        $_SESSION['flash'] = "Chapitre ajouté avec succès.";
        header('Location: /manage_chapters');
        exit;
    }

    public function editChapter()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID de chapitre manquant.';
            header('Location: /manage_chapters');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();
        $q = $db->prepare('SELECT * FROM Chapter WHERE id = :id');
        $q->execute(['id' => $id]);
        $chapter = $q->fetch(PDO::FETCH_ASSOC);

        if (!$chapter) {
            $_SESSION['flash'] = 'Chapitre introuvable.';
            header('Location: /manage_chapters');
            exit;
        }

        require 'view/adminEditChapter.php';
    }

    public function updateChapter()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID de chapitre manquant.';
            header('Location: /manage_chapters');
            exit;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image = $_POST['image_path'] ?? null;

        require_once 'core/Database.php';
        $db = getDB();

        $q = $db->prepare('UPDATE Chapter SET title = :title, description = :description, image = :image WHERE id = :id');
        $q->execute([
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'id' => $id
        ]);

        $_SESSION['flash'] = 'Chapitre mis à jour.';
        header('Location: /manage_chapters');
        exit;
    }

    public function manageUsers()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }
        
        require 'view/adminManageAccounts.php';
    }

    public function editUser()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID de compte manquant.';
            header('Location: /manage_accounts');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();
        $q = $db->prepare('SELECT id, username, admin FROM Account WHERE id = :id');
        $q->execute(['id' => $id]);
        $account = $q->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            $_SESSION['flash'] = 'Compte introuvable.';
            header('Location: /manage_accounts');
            exit;
        }

        require 'view/adminEditUser.php';
    }

    public function updateUser()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID de compte manquant.';
            header('Location: /manage_accounts');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '') {
            $_SESSION['flash'] = 'Le nom d\'utilisateur ne peut pas être vide.';
            header('Location: /manage_accounts/edit?id=' . urlencode($id));
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $q = $db->prepare('SELECT id FROM Account WHERE username = :username AND id != :id');
        $q->execute(['username' => $username, 'id' => $id]);
        $exists = $q->fetchColumn();
        if ($exists) {
            $_SESSION['flash'] = 'Ce nom d\'utilisateur est déjà utilisé.';
            header('Location: /manage_accounts/edit?id=' . urlencode($id));
            exit;
        }

        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $q = $db->prepare('UPDATE Account SET username = :username, password_hash = :password WHERE id = :id');
            $q->execute(['username' => $username, 'password' => $passwordHash, 'id' => $id]);
        } else {
            $q = $db->prepare('UPDATE Account SET username = :username WHERE id = :id');
            $q->execute(['username' => $username, 'id' => $id]);
        }

        $_SESSION['flash'] = 'Compte mis à jour.';
        header('Location: /manage_accounts');
        exit;
    }

    public function deleteUser()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID de compte manquant.';
            header('Location: /manage_accounts');
            exit;
        }

    /* permet de ne pas supprimer son propre compte */
        if ((int)$id === (int)($_SESSION['id'] ?? 0)) {
            $_SESSION['flash'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            header('Location: /manage_accounts');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $q = $db->prepare('DELETE FROM Account WHERE id = :id');
        $q->execute(['id' => $id]);

        $_SESSION['flash'] = 'Compte supprimé.';
        header('Location: /manage_accounts');
        exit;
    }
}
