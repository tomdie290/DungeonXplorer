<?php
require_once __DIR__ . '/../core/Database.php';

class Inventory
{
    /**
     * Récupère l'inventaire d'un héros
     */
    public function getInventory(int $heroId): array
    {
        $db = getDB();

        $stmt = $db->prepare("
            SELECT i.quantity, it.name, it.description, it.item_type
            FROM Inventory i
            JOIN Items it ON i.item_id = it.id
            WHERE i.hero_id = ?
        ");
        $stmt->execute([$heroId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
