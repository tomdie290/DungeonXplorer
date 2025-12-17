<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'models/Hero.php';
require_once 'models/Inventory.php';

// Vérifie le héros
if (!isset($_SESSION['hero_id'])) die("Erreur : héros non sélectionné");
$hero = Hero::loadById($_SESSION['hero_id']);
if (!$hero) die("Erreur : héros introuvable");

// Si on passe un paramètre inCombat (1 = en combat)
$inCombat = isset($_GET['inCombat']) && $_GET['inCombat'] == 1;

// Récupération de l’inventaire
$inventoryModel = new Inventory();
$inventory = $inventoryModel->getInventory($hero->id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <title>Inventaire de <?= htmlspecialchars($hero->name) ?></title>
    <style>
        .inventory-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 25px;
            background: #262626;
            border: 2px solid #C4975E;
            border-radius: 12px;
            box-shadow: 0 0 25px #C4975E40;
        }
        .inventory-title {
            font-family: "Pirata One", system-ui;
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 2rem;
            color: #E5E5E5;
        }
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        .inventory-item {
            background-color: #2E2E2E;
            border: 2px solid #C4975E;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(196, 151, 94, 0.25);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .inventory-item:hover {
            transform: scale(1.03);
            box-shadow: 0 0 25px rgba(196, 151, 94, 0.5);
        }
        .inventory-item h3 {
            font-family: "Pirata One", system-ui;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: #FFD700;
        }
        .inventory-item p {
            margin: 3px 0;
            color: #E5E5E5;
            font-size: 0.95rem;
        }
        .inventory-item em {
            color: #ccc;
        }
        .back-btn {
            display: block;
            width: max-content;
            margin: 30px auto 0 auto;
            padding: 10px 20px;
            color: #E5E5E5;
            border: 2px solid #C4975E;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.2s;
        }
        .back-btn:hover {
            background-color: #8B1E1E;
            color: white;
        }
        .empty-message {
            text-align: center;
            font-size: 1.2rem;
            color: #ccc;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="inventory-container">
        <h2 class="inventory-title">Inventaire de <?= htmlspecialchars($hero->name) ?></h2>

        <?php if (empty($inventory)): ?>
            <p class="empty-message">L'inventaire est vide.</p>
        <?php else: ?>
            <div class="inventory-grid">
                <?php foreach ($inventory as $item): ?>
                    <div class="inventory-item">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p><strong>Type :</strong> <?= htmlspecialchars($item['item_type']) ?></p>
                        <p><strong>Quantité :</strong> <?= (int)$item['quantity'] ?></p>
                        <p><em><?= htmlspecialchars($item['description']) ?></em></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$inCombat): ?>
            <a href="account" class="back-btn">Retour au compte</a>
        <?php endif; ?>
    </div>
</body>
</html>
