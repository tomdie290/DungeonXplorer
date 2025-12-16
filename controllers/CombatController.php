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

        // Récupère l'id du chapitre suivant (premier lien) pour la redirection après combat
        $nextChapterId = null;
        $nextLinkId = null;
        $nextLinkText = null;
        $deathChapterId = null;
        $deathLinkId = null;
        $deathLinkText = null;
        try {
            require_once __DIR__ . '/../core/Database.php';
            $db = getDB();
            $stmt = $db->prepare("SELECT id, next_chapter_id, description FROM Links WHERE chapter_id = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$chapterId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $nextLinkId = (int)$row['id'];
                $nextChapterId = $row['next_chapter_id'] !== null ? (int)$row['next_chapter_id'] : null;
                $nextLinkText = $row['description'] ?? null;
            }

            // Cherche le lien de mort associé au chapitre courant (next_chapter_id = 10)
            $deathTarget = 10; // id du chapitre 'mort'
            $stmt2 = $db->prepare("SELECT id, next_chapter_id, description FROM Links WHERE chapter_id = ? AND next_chapter_id = ? LIMIT 1");
            $stmt2->execute([$chapterId, $deathTarget]);
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row2) {
                $deathLinkId = (int)$row2['id'];
                $deathChapterId = $row2['next_chapter_id'] !== null ? (int)$row2['next_chapter_id'] : null;
                $deathLinkText = $row2['description'] ?? null;
            } else {
                // fallback: cherche n'importe quel lien qui pointe vers le chapitre de mort
                $stmt3 = $db->prepare("SELECT id, next_chapter_id, description FROM Links WHERE next_chapter_id = ? LIMIT 1");
                $stmt3->execute([$deathTarget]);
                $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
                if ($row3) {
                    $deathLinkId = (int)$row3['id'];
                    $deathChapterId = $row3['next_chapter_id'] !== null ? (int)$row3['next_chapter_id'] : null;
                    $deathLinkText = $row3['description'] ?? null;
                }
            }
        } catch (Exception $e) {
            $nextChapterId = null;
            $nextLinkId = null;
            $deathChapterId = null;
            $deathLinkId = null;
            $nextLinkText = null;
            $deathLinkText = null;
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
            // Reset hero to max
            $hero->pv = $hero->pv_max;
            $hero->mana = $hero->mana_max;
            $hero->save();
            header("Location: /DungeonXplorer/chapter");
        }
        exit;
    }
}
