<?php

class AccountController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            header("Location: login");
        } else {
            require_once 'view/account.php';
        }
    }

    public function deleteHero() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id'])) {
            header("Location: login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hero_id'])) {
            $heroId = (int)$_POST['hero_id'];
            $accountId = $_SESSION['id'];

            require_once 'core/Database.php';
            $db = getDB();

            // Vérifier que le héros appartient au compte
            $stmt = $db->prepare("SELECT id FROM Hero WHERE id = ? AND account_id = ?");
            $stmt->execute([$heroId, $accountId]);
            if ($stmt->fetch()) {
                // Supprimer le héros (les cascades s'occuperont des aventures, inventaires, etc.)
                $stmt = $db->prepare("DELETE FROM Hero WHERE id = ?");
                $stmt->execute([$heroId]);
            }
        }

        header("Location: account");
        exit;
    }

    public function deleteAccount() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id'])) {
            header("Location: login");
            exit;
        }

        // Only accept POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: profil");
            exit;
        }

        $accountId = isset($_POST['account_id']) ? (int)$_POST['account_id'] : 0;
        // Ensure the account matches the logged user
        if ($accountId !== (int)$_SESSION['id']) {
            header("Location: profil");
            exit;
        }

        require_once 'core/Database.php';
        $db = getDB();
        // Delete account (cascades heroes, adventures, etc.)
        $stmt = $db->prepare("DELETE FROM Account WHERE id = ?");
        $stmt->execute([$accountId]);

        // Set flash message then destroy session and redirect to home/login
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = 'Votre compte a été supprimé.';
        header("Location: /DungeonXplorer/home");
        // Destroy session after redirect (best effort)
        $_SESSION = [];
        if (session_status() !== PHP_SESSION_NONE) session_destroy();
        exit;
    }
}
?>