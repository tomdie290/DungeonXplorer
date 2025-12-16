<?php

class InventoryController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id'])) {
            header("Location: login");
            exit;
        }

        $accountId = $_SESSION['id'];
        $heroId = $_GET['hero'] ?? null;

        if (!$heroId) {
            die("Héros non spécifié");
        }

        require_once 'core/Database.php';
        $db = getDB();

        // Vérifier que le héros appartient au compte
        $stmt = $db->prepare("SELECT id, name FROM Hero WHERE id = ? AND account_id = ?");
        $stmt->execute([$heroId, $accountId]);
        $hero = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hero) {
            die("Héros introuvable ou accès refusé");
        }

        // Récupérer l'inventaire
        $stmt = $db->prepare("
            SELECT i.quantity, it.name, it.description, it.item_type
            FROM Inventory i
            JOIN Items it ON i.item_id = it.id
            WHERE i.hero_id = ?
        ");
        $stmt->execute([$heroId]);
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'view/inventory.php';
    }
}
?>