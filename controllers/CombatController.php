<?php
require_once 'models/Hero.php';
require_once 'models/Monster.php';

class CombatController
{
    public function start(Monster $monster, int $chapterId, array $snapshot = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['hero_id'])) {
            die("Erreur : héros non sélectionné");
        }

        $hero = Hero::loadById($_SESSION['hero_id']);
        if (!$hero) {
            die("Erreur : héros introuvable");
        }

        // Si on reprend un combat depuis une sauvegarde en session, appliquer l'état
        $heroTurnResume = null;
        if (is_array($snapshot)) {
            if (isset($snapshot['hero_pv'])) $hero->pv = (int)$snapshot['hero_pv'];
            if (isset($snapshot['hero_mana'])) $hero->mana = (int)$snapshot['hero_mana'];
            if (isset($snapshot['hero_turn'])) $heroTurnResume = (bool)$snapshot['hero_turn'];
            if (isset($snapshot['monster_pv'])) $monster->pv = (int)$snapshot['monster_pv'];
        }

        // Récupère les liens du chapitre et identifie le lien "gagnant" et le lien de "mort"
        $nextChapterId = null;
        $nextLinkId = null;
        $nextLinkText = null;
        $deathChapterId = null;
        $deathLinkId = null;
        $deathLinkText = null;
        try {
            require_once __DIR__ . '/../core/Database.php';
            $db = getDB();
            $stmt = $db->prepare("SELECT id, next_chapter_id, description FROM Links WHERE chapter_id = ? ORDER BY id ASC");
            $stmt->execute([$chapterId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Parcourt tous les liens afin d'identifier celui de mort (description contenant 'mort')
            foreach ($rows as $r) {
                $desc = $r['description'] ?? '';
                if (stripos($desc, 'mort') !== false) {
                    $deathLinkId = (int)$r['id'];
                    $deathChapterId = $r['next_chapter_id'] !== null ? (int)$r['next_chapter_id'] : null;
                    $deathLinkText = $desc;
                    continue;
                }
                // Premier lien non-mort devient le lien de progression
                if ($nextLinkId === null) {
                    $nextLinkId = (int)$r['id'];
                    $nextChapterId = $r['next_chapter_id'] !== null ? (int)$r['next_chapter_id'] : null;
                    $nextLinkText = $desc;
                }
            }

            // Si aucun lien non-mort trouvé mais il existe des liens, prendre le premier comme progression
            if ($nextLinkId === null && !empty($rows)) {
                $r = $rows[0];
                $nextLinkId = (int)$r['id'];
                $nextChapterId = $r['next_chapter_id'] !== null ? (int)$r['next_chapter_id'] : null;
                $nextLinkText = $r['description'] ?? null;
            }
        } catch (Exception $e) {
            $nextChapterId = null;
            $nextLinkId = null;
            $deathChapterId = null;
            $deathLinkId = null;
            $nextLinkText = null;
            $deathLinkText = null;
        }

        // Detect if there's an active damage boost for this hero's current adventure
        $heroDamageBoost = 0;
        try {
            $stmtA = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL LIMIT 1");
            $stmtA->execute([$hero->id]);
            $advId = $stmtA->fetchColumn();
            if ($advId && isset($_SESSION['damage_boost']) && !empty($_SESSION['damage_boost'][$advId])) {
                $heroDamageBoost = (int)$_SESSION['damage_boost'][$advId];
            }
        } catch (Exception $e) {
            // ignore
        }

        // Passe les variables à la vue
        // Passe le flag de reprise à la vue via $heroTurnResume
        require __DIR__ . '/../view/combat.php';
    }

    public function endCombat(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['hero_id'])) {
            die("Erreur : héros non sélectionné");
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $result = $data['result'] ?? '';
        $hero_pv = (int)($data['hero_pv'] ?? 0);
        $hero_mana = (int)($data['hero_mana'] ?? 0);

        $hero = Hero::loadById($_SESSION['hero_id']);
        if (!$hero) {
            die("Erreur : héros introuvable");
        }

        $hero->pv = $hero_pv;
        $hero->mana = $hero_mana;
        $hero->save();

        // Supprime l'éventuel snapshot de combat en session pour l'aventure en cours
        try {
            require_once __DIR__ . '/../core/Database.php';
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL");
            $stmt->execute([$_SESSION['hero_id']]);
            $adventureId = $stmt->fetchColumn();
            if ($adventureId && isset($_SESSION['combat_snapshot'][$adventureId])) {
                unset($_SESSION['combat_snapshot'][$adventureId]);
            }
        } catch (Exception $e) {
            // ignore
        }

        if ($result === 'win') {
            header("Location: /DungeonXplorer/chapter");
        } else {
            // Mark death: clear potions and set a flag so subsequent save is treated as after-death
            try {
                $db = getDB();
                $stmtA = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL LIMIT 1");
                $stmtA->execute([$_SESSION['hero_id']]);
                $adventureId = $stmtA->fetchColumn();
                if ($adventureId) {
                    if (!isset($_SESSION['just_died'])) $_SESSION['just_died'] = [];
                    $_SESSION['just_died'][$adventureId] = true;
                }
                // Remove all potions from the hero's inventory
                $stmtDel = $db->prepare("DELETE FROM Inventory WHERE hero_id = ? AND item_id IN (SELECT id FROM Items WHERE item_type = 'potion')");
                $stmtDel->execute([$_SESSION['hero_id']]);
                $_SESSION['flash'] = 'Vous avez perdu toutes vos potions en mourant. Vous pourrez en racheter après avoir quitté et sauvegardé.';
            } catch (Exception $e) {
                // ignore DB errors
            }

            // Reset hero to max
            $hero->pv = $hero->pv_max;
            $hero->mana = $hero->mana_max;
            $hero->save();
            header("Location: /DungeonXplorer/chapter");
        }
        exit;
    }
}
