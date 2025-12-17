<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../models/Hero.php';
require_once __DIR__ . '/../models/Inventory.php';

class InventoryController
{
    private Inventory $inventoryModel;

    public function __construct()
    {
        $this->inventoryModel = new Inventory();
    }

    public function index()
    {
        if (!isset($_SESSION['id'])) {
            header("Location: login");
            exit;
        }

        $accountId = $_SESSION['id'];
        $heroId = $_GET['hero'] ?? null;
        if (!$heroId) die("Héros non spécifié");

        $hero = Hero::loadById((int)$heroId);
        if (!$hero || $hero->account_id !== $accountId) {
            die("Héros introuvable ou accès refusé");
        }

        $inventory = $this->inventoryModel->getInventory($heroId);

        require __DIR__ . '/../view/inventory.php';
    }
}
