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
}
?>