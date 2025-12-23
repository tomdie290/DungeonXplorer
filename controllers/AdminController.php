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

    public function manageImages()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $imgDir = __DIR__ . '/../img';
        $images = [];
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $f) {
                if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']) && is_file($imgDir . '/' . $f)) {
                    $images[] = $f;
                }
            }
        }

        require 'view/adminManageImages.php';
    }

    public function uploadImage()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = 'Aucun fichier téléchargé ou erreur lors de la mise en ligne.';
            header('Location: /manage_images');
            exit;
        }

        $imgDir = __DIR__ . '/../img';
        if (!is_dir($imgDir) || !is_writable($imgDir)) {
            $_SESSION['flash'] = 'Le dossier d\'images n\'existe pas ou n\'est pas accessible en écriture.';
            header('Location: /manage_images');
            exit;
        }

        $tmp = $_FILES['image']['tmp_name'];
        $origName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $_SESSION['flash'] = 'Type de fichier non autorisé.';
            header('Location: /manage_images');
            exit;
        }

        $info = @getimagesize($tmp);
        if ($info === false) {
            $_SESSION['flash'] = 'Le fichier téléchargé n\'est pas une image valide.';
            header('Location: /manage_images');
            exit;
        }

        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $origName);
        $target = $imgDir . '/' . $safe;
        $i = 1;
        while (file_exists($target)) {
            $safe = pathinfo($safe, PATHINFO_FILENAME) . '-' . $i . '.' . $ext;
            $target = $imgDir . '/' . $safe;
            $i++;
        }

        if (!move_uploaded_file($tmp, $target)) {
            $_SESSION['flash'] = 'Impossible d\'enregistrer l\'image.';
            header('Location: /manage_images');
            exit;
        }

        $_SESSION['flash'] = 'Image téléchargée.';
        header('Location: /manage_images');
        exit;
    }

    public function deleteImage()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /account');
            exit;
        }

        $file = $_POST['file'] ?? null;
        if (!$file) {
            $_SESSION['flash'] = 'Fichier manquant.';
            header('Location: /manage_images');
            exit;
        }

        $basename = basename($file);
        if ($basename !== $file) {
            $_SESSION['flash'] = 'Nom de fichier invalide.';
            header('Location: /manage_images');
            exit;
        }

        $imgDir = __DIR__ . '/../img';
        $path = $imgDir . '/' . $basename;
        if (!is_file($path)) {
            $_SESSION['flash'] = 'Fichier introuvable.';
            header('Location: /manage_images');
            exit;
        }

        if (!unlink($path)) {
            $_SESSION['flash'] = 'Impossible de supprimer le fichier.';
            header('Location: /manage_images');
            exit;
        }

        $_SESSION['flash'] = 'Image supprimée.';
        header('Location: /manage_images');
        exit;
    }

    public function manageMonsters()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();
        $q = $db->prepare('SELECT * FROM Monster ORDER BY id');
        $q->execute();
        $monsters = $q->fetchAll(PDO::FETCH_ASSOC);

        require 'view/adminManageMonsters.php';
    }

    public function storeMonster()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $pv = (int)($_POST['pv'] ?? 0);
        $mana = (int)($_POST['mana'] ?? 0);
        $strength = (int)($_POST['strength'] ?? 0);
        $initiative = (int)($_POST['initiative'] ?? 0);
        $attack_text = trim($_POST['attack_text'] ?? '');
        $xp_reward = (int)($_POST['xp_reward'] ?? 0);
        $image = !empty($_POST['image_path']) ? 'img/' . trim($_POST['image_path']) : null;

        if ($name === '' || $pv <= 0 || $strength <= 0) {
            $_SESSION['flash'] = 'Données invalides.';
            header('Location: /manage_monsters');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $q = $db->prepare('INSERT INTO Monster (name, pv, mana, strength, initiative, attack_text, xp_reward, image) VALUES (:name, :pv, :mana, :strength, :initiative, :attack_text, :xp_reward, :image)');
        $q->execute([
            'name' => $name,
            'pv' => $pv,
            'mana' => $mana,
            'strength' => $strength,
            'initiative' => $initiative,
            'attack_text' => $attack_text,
            'xp_reward' => $xp_reward,
            'image' => $image
        ]);

        $_SESSION['flash'] = 'Monstre créé.';
        header('Location: /manage_monsters');
        exit;
    }

    public function editMonster()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID manquant.';
            header('Location: /manage_monsters');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();
        $q = $db->prepare('SELECT * FROM Monster WHERE id = :id');
        $q->execute(['id' => $id]);
        $monster = $q->fetch(PDO::FETCH_ASSOC);

        if (!$monster) {
            $_SESSION['flash'] = 'Monstre introuvable.';
            header('Location: /manage_monsters');
            exit;
        }

        require 'view/adminEditMonster.php';
    }

    public function updateMonster()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID manquant.';
            header('Location: /manage_monsters');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $pv = (int)($_POST['pv'] ?? 0);
        $mana = (int)($_POST['mana'] ?? 0);
        $strength = (int)($_POST['strength'] ?? 0);
        $initiative = (int)($_POST['initiative'] ?? 0);
        $attack_text = trim($_POST['attack_text'] ?? '');
        $xp_reward = (int)($_POST['xp_reward'] ?? 0);
        $image = !empty($_POST['image_path']) ? 'img/' . trim($_POST['image_path']) : null;

        if ($name === '' || $pv <= 0 || $strength <= 0) {
            $_SESSION['flash'] = 'Données invalides.';
            header('Location: /manage_monsters/edit?id=' . urlencode($id));
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $q = $db->prepare('UPDATE Monster SET name = :name, pv = :pv, mana = :mana, strength = :strength, initiative = :initiative, attack_text = :attack_text, xp_reward = :xp_reward, image = :image WHERE id = :id');
        $q->execute([
            'name' => $name,
            'pv' => $pv,
            'mana' => $mana,
            'strength' => $strength,
            'initiative' => $initiative,
            'attack_text' => $attack_text,
            'xp_reward' => $xp_reward,
            'image' => $image,
            'id' => $id
        ]);

        $_SESSION['flash'] = 'Monstre mis à jour.';
        header('Location: /manage_monsters');
        exit;
    }

    public function deleteMonster()
    {
        session_start();
        if ($_SESSION['admin'] != 1) {
            header('Location: /login');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['flash'] = 'ID manquant.';
            header('Location: /manage_monsters');
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();

        $q = $db->prepare('DELETE FROM Monster WHERE id = :id');
        $q->execute(['id' => $id]);

        $_SESSION['flash'] = 'Monstre supprimé.';
        header('Location: /manage_monsters');
        exit;
    }
}
