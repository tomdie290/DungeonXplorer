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

        // Set session hero id for compatibility with views that expect it
        $_SESSION['hero_id'] = $hero->id;

        $inventory = $this->inventoryModel->getInventory($heroId);

        // If onlyPotions flag is set, render the same view but it's used by combat.js to extract potion cards
        require __DIR__ . '/../view/inventory.php';
    }

    public function addPotion(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /DungeonXplorer/account');
            exit;
        }

        if (!isset($_SESSION['id'])) {
            header('Location: /DungeonXplorer/login');
            exit;
        }

        $accountId = $_SESSION['id'];
        $heroId = isset($_POST['hero_id']) ? (int)$_POST['hero_id'] : null;
        $type = $_POST['potion_type'] ?? '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        if ($quantity <= 0) $quantity = 1;

        if (!$heroId || !$type) {
            $_SESSION['flash'] = 'Données invalides pour l\'ajout de potion.';
            header('Location: /DungeonXplorer/account');
            exit;
        }

        $hero = Hero::loadById($heroId);
        if (!$hero || $hero->account_id !== $accountId) {
            $_SESSION['flash'] = 'Héros introuvable ou accès refusé.';
            header('Location: /DungeonXplorer/account');
            exit;
        }

        // Prepare DB connection early
        $db = getDB();

        // If the hero's most recent adventure progress is 'Saved', block potion modifications.
        // Exception: if the last saved progress is on a death chapter, allow potion changes (player saved after dying).
        $stmtLast = $db->prepare("SELECT ap.status, ap.chapter_id FROM Adventure_Progress ap JOIN Adventure a ON ap.adventure_id = a.id WHERE a.hero_id = ? ORDER BY ap.visit_date DESC LIMIT 1");
        $stmtLast->execute([$heroId]);
        $lastRow = $stmtLast->fetch(PDO::FETCH_ASSOC);
        $lastStatus = $lastRow['status'] ?? null;
        $lastChapterId = isset($lastRow['chapter_id']) ? (int)$lastRow['chapter_id'] : null;
        if ($lastStatus === 'Saved') {
            $isDeathSave = false;
            if ($lastChapterId) {
                // Check chapter title/description for death keywords
                $stmtCh = $db->prepare("SELECT title, description FROM Chapter WHERE id = ? LIMIT 1");
                $stmtCh->execute([$lastChapterId]);
                $ch = $stmtCh->fetch(PDO::FETCH_ASSOC);
                $title = $ch['title'] ?? '';
                $descCh = $ch['description'] ?? '';
                if (stripos($title, 'mort') !== false || stripos($descCh, 'mort') !== false) {
                    $isDeathSave = true;
                }

                // Also check if any link from that chapter explicitly mentions 'mort'
                if (!$isDeathSave) {
                    $stmtL = $db->prepare("SELECT description FROM Links WHERE chapter_id = ?");
                    $stmtL->execute([$lastChapterId]);
                    $links = $stmtL->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($links as $ln) {
                        if (isset($ln['description']) && stripos($ln['description'], 'mort') !== false) {
                            $isDeathSave = true;
                            break;
                        }
                    }
                }
            }

            if (!$isDeathSave) {
                $_SESSION['flash'] = 'Impossible de modifier les potions : l\'aventure a été quittée et sauvegardée.';
                header('Location: /DungeonXplorer/account');
                exit;
            }
        }

        // Count distinct potion types already owned
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(DISTINCT it.id) FROM Inventory i JOIN Items it ON i.item_id = it.id WHERE i.hero_id = ? AND it.item_type = 'potion'");
        $stmt->execute([$heroId]);
        $count = (int)$stmt->fetchColumn();

        // If the submitted value is numeric, treat it as an existing item_id to increment
        if (ctype_digit((string)$type)) {
            $itemId = (int)$type;
            // verify that this item is actually a potion and exists
            $stmtI = $db->prepare("SELECT id, name FROM Items WHERE id = ? AND item_type = 'potion' LIMIT 1");
            $stmtI->execute([$itemId]);
            $foundItem = $stmtI->fetch(PDO::FETCH_ASSOC);
            if (!$foundItem) {
                $_SESSION['flash'] = 'Item de potion introuvable.';
                header('Location: /DungeonXplorer/account');
                exit;
            }
            // If item is a power boost, ensure only one may exist for this hero
            if (stripos($foundItem['name'], 'Puissance') !== false) {
                $stmtChk = $db->prepare("SELECT quantity FROM Inventory WHERE hero_id = ? AND item_id = ? LIMIT 1");
                $stmtChk->execute([$heroId, $itemId]);
                if ($stmtChk->fetchColumn()) {
                    $_SESSION['flash'] = 'Vous possédez déjà cet élixir unique.';
                    header('Location: /DungeonXplorer/account');
                    exit;
                }
                $quantity = 1;
            }
        } else {
            // Map potion types to definitions
            $definitions = [
                'small_heal' => ['name' => 'Petite potion de soin (+10 PV)', 'desc' => 'Restaure 10 PV (soin:10)'],
                'big_heal' => ['name' => 'Grosse potion de soin (+25 PV)', 'desc' => 'Restaure 25 PV (soin:25)'],
                'mana' => ['name' => 'Potion de mana (+15 Mana)', 'desc' => 'Restaure 15 Mana (mana:15)'],
                // Nouveau: potion d'Elixir de Puissance -> +10% dégâts pour toute l'aventure (usage unique)
                'power_boost' => ['name' => 'Elixir de Puissance (+10% dégâts, unique)', 'desc' => 'boost:10'],
                // Nouveau: potion de force -> augmente la force permanente
                'strength' => ['name' => 'Potion de Force (Inflige 20 dégâts)', 'desc' => 'dmg:20'],
            ];

            if (!isset($definitions[$type])) {
                $_SESSION['flash'] = 'Type de potion inconnu.';
                header('Location: /DungeonXplorer/account');
                exit;
            }

            $def = $definitions[$type];

            // Check if item already exists in Items table
            $stmtI = $db->prepare("SELECT id FROM Items WHERE name = ? LIMIT 1");
            $stmtI->execute([$def['name']]);
            $itemId = $stmtI->fetchColumn();
            if (!$itemId) {
                $stmtIns = $db->prepare("INSERT INTO Items (name, description, item_type) VALUES (?, ?, 'potion')");
                $stmtIns->execute([$def['name'], $def['desc']]);
                $itemId = $db->lastInsertId();
            }

            // If this is the special power_boost, enforce single copy per hero
            if ($type === 'power_boost') {
                $stmtChk = $db->prepare("SELECT quantity FROM Inventory WHERE hero_id = ? AND item_id = ? LIMIT 1");
                $stmtChk->execute([$heroId, $itemId]);
                if ($stmtChk->fetchColumn()) {
                    $_SESSION['flash'] = 'Vous ne pouvez posséder qu\'un seul Elixir de Puissance.';
                    header('Location: /DungeonXplorer/account');
                    exit;
                }
                $quantity = 1; // always single
            }

            // If it's a new distinct potion and we already have 2, refuse
            // But if the hero already has this potion type, allow incrementing quantity
             $stmtExists = $db->prepare("SELECT quantity FROM Inventory WHERE hero_id = ? AND item_id = ? LIMIT 1");
             $stmtExists->execute([$heroId, $itemId]);
             $existing = $stmtExists->fetch(PDO::FETCH_ASSOC);

             if (!$existing && $count >= 2) {
                 $_SESSION['flash'] = 'Vous ne pouvez posséder que deux types de potions différentes.';
                 header('Location: /DungeonXplorer/account');
                 exit;
             }
        }

        // Cap quantities to 10
        if ($quantity > 10) {
            $quantity = 10;
            $_SESSION['flash'] = 'Quantité limitée à 10 par type de potion.';
        }

        // At this point we have a valid $itemId; check if the hero already has this item
        $stmtExists2 = $db->prepare("SELECT quantity FROM Inventory WHERE hero_id = ? AND item_id = ? LIMIT 1");
        $stmtExists2->execute([$heroId, $itemId]);
        $existing2 = $stmtExists2->fetch(PDO::FETCH_ASSOC);

        if ($existing2) {
            // Once a potion type is added, its quantity cannot be modified
            $_SESSION['flash'] = 'Impossible de modifier la quantité d\'une potion une fois ajoutée.';
            header('Location: /DungeonXplorer/account');
            exit;
        } else {
            $stmtInsInv = $db->prepare("INSERT INTO Inventory (hero_id, item_id, quantity) VALUES (?, ?, ?)");
            $stmtInsInv->execute([$heroId, $itemId, $quantity]);
        }

        // fetch item name for friendly message
        $stmtName = $db->prepare("SELECT name FROM Items WHERE id = ? LIMIT 1");
        $stmtName->execute([$itemId]);
        $itemName = $stmtName->fetchColumn() ?: 'Potion';

        $_SESSION['flash'] = $quantity . ' x ' . $itemName . ' ajoutée(s) à l\'inventaire.';
        header('Location: /DungeonXplorer/account');
        exit;
    }

    public function use(): void {
        // Expect JSON POST { item_id }
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body || !isset($body['item_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing item_id']);
            exit;
        }

        if (!isset($_SESSION['hero_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Hero not selected']);
            exit;
        }

        $hero = Hero::loadById($_SESSION['hero_id']);
        if (!$hero) {
            http_response_code(404);
            echo json_encode(['error' => 'Hero not found']);
            exit;
        }

        $itemId = (int)$body['item_id'];
        $db = getDB();
        $stmt = $db->prepare("SELECT i.quantity, it.name, it.description, it.item_type FROM Inventory i JOIN Items it ON i.item_id = it.id WHERE i.hero_id = ? AND it.id = ? LIMIT 1");
        $stmt->execute([$hero->id, $itemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['quantity'] <= 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
            exit;
        }

        // Parse effect tokens from description (soin:, mana:, boost:, str:)
        $desc = $row['description'] ?? '';
        $applied = [];
        if (preg_match('/soin:(\d+)/i', $desc, $m)) {
            $value = (int)$m[1];
            if ($value > 0) {
                $hero->pv = min((int)$hero->pv_max, (int)$hero->pv + $value);
                $hero->save();
                $applied['pv'] = $value;
            }
        } elseif (preg_match('/mana:(\d+)/i', $desc, $m)) {
            $value = (int)$m[1];
            if ($value > 0) {
                $hero->mana = min((int)$hero->mana_max, (int)$hero->mana + $value);
                $hero->save();
                $applied['mana'] = $value;
            }
        } elseif (preg_match('/boost:(\d+)/i', $desc, $m)) {
            $pct = (int)$m[1];
            // Need an active adventure to attach the boost to
            $stmtA = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL LIMIT 1");
            $stmtA->execute([$hero->id]);
            $adventureId = $stmtA->fetchColumn();
            if (!$adventureId) {
                http_response_code(400);
                echo json_encode(['error' => 'Doit être utilisé en aventure active']);
                exit;
            }
            if (!isset($_SESSION['damage_boost'])) $_SESSION['damage_boost'] = [];
            if (!empty($_SESSION['damage_boost'][$adventureId])) {
                http_response_code(400);
                echo json_encode(['error' => 'Un boost de dégâts est déjà actif pour cette aventure']);
                exit;
            }
            $_SESSION['damage_boost'][$adventureId] = $pct;
            $applied['boost'] = $pct;
        } elseif (preg_match('/dmg:(\d+)/i', $desc, $m)) {
            $val = (int)$m[1];
            if ($val > 0) {
                // indicate to client to apply damage to the current monster
                $applied['monster_damage'] = $val;
            }
        } elseif (preg_match('/str:(\d+)/i', $desc, $m)) {
            $val = (int)$m[1];
            if ($val > 0) {
                $hero->strength = (int)$hero->strength + $val;
                $hero->save();
                $applied['strength'] = $val;
            }
        } else {
            // fallback: try to detect numbers in name as heal
            if (preg_match('/(\d+)/', $row['name'], $m)) {
                $value = (int)$m[1];
                if ($value > 0) {
                    $hero->pv = min((int)$hero->pv_max, (int)$hero->pv + $value);
                    $hero->save();
                    $applied['pv'] = $value;
                }
            }
        }

        // decrement quantity
        $stmtDec = $db->prepare("UPDATE Inventory SET quantity = quantity - 1 WHERE hero_id = ? AND item_id = ?");
        $stmtDec->execute([$hero->id, $itemId]);
        $stmtClean = $db->prepare("DELETE FROM Inventory WHERE hero_id = ? AND item_id = ? AND quantity <= 0");
        $stmtClean->execute([$hero->id, $itemId]);

        // Return applied effects for UI clarity
        $resp = ['status' => 'ok', 'pv' => $hero->pv, 'mana' => $hero->mana];
        if (!empty($applied)) $resp['applied'] = $applied;
        echo json_encode($resp);
        exit;
    }
}
