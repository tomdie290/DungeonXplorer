<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'models/Chapter.php';
require_once 'models/Hero.php';
require_once 'models/Monster.php';
require_once 'controllers/CombatController.php';
require_once 'core/Database.php';

class ChapterController {
    private array $chapters = [];

    public function __construct() {
        $db = getDB();
        $stmt = $db->query("
            SELECT c.*, l.id AS link_id, l.description AS link_text, l.next_chapter_id
            FROM Chapter c
            LEFT JOIN Links l ON l.chapter_id = c.id
            ORDER BY c.id ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $id = $row['id'];
            if (!isset($this->chapters[$id])) {
                $this->chapters[$id] = new Chapter(
                    $id,
                    $row['title'],
                    $row['description'],
                    $row['image'],
                    []
                );
            }

            if (!empty($row['link_id'])) {
                $this->chapters[$id]->addChoice([
                    'id' => $row['link_id'],
                    'text' => $row['link_text'] ?? 'Continuer',
                    'chapter' => $row['next_chapter_id']
                ]);
            }
        }
    }

    public function show(int $id): void {
        if (!isset($_SESSION['hero_id'])) die("Erreur : héros non sélectionné");
        $hero = Hero::loadById($_SESSION['hero_id']);
        if (!$hero) die("Erreur : héros introuvable");

        // Récupère l'aventure en cours pour ce héros (si existante)
        $db = getDB();
        $stmtAdv = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL");
        $stmtAdv->execute([$_SESSION['hero_id']]);
        $currentAdventureId = $stmtAdv->fetchColumn();

        $chapter = $this->getChapter($id);
        if (!$chapter) { 
            require 'view/404.php'; 
            return; 
        }

        // Vérifie s’il y a un monstre dans ce chapitre
        $monster = Monster::loadByChapter($chapter->getId());
        if ($monster !== null) {
            $combat = new CombatController();
            // Si on a un snapshot de combat en session pour cette aventure, le fournir
            $snapshot = null;
            if ($currentAdventureId && isset($_SESSION['combat_snapshot']) && isset($_SESSION['combat_snapshot'][$currentAdventureId])) {
                $snap = $_SESSION['combat_snapshot'][$currentAdventureId];
                // s'assurer que le snapshot correspond au même chapitre
                if (isset($snap['chapter_id']) && (int)$snap['chapter_id'] === (int)$chapter->getId()) {
                    $snapshot = $snap;
                }
            }
            $combat->start($monster, $chapter->getId(), $snapshot);
            return;
        }

        include 'view/chapter.php';
    }

    public function choice(): void {
        if (!isset($_POST['choice_id'])) {
            header("Location: /DungeonXplorer/chapter");
            exit;
        }

        if (!isset($_SESSION['hero_id'])) die("Erreur : héros non sélectionné");

        $choiceId = intval($_POST['choice_id']);
        $db = getDB();
        $stmt = $db->prepare("SELECT next_chapter_id, description FROM Links WHERE id = ?");
        $stmt->execute([$choiceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next = $row['next_chapter_id'] ?? null;
        $desc = $row['description'] ?? '';

        if (!$next) die("Choix invalide");

        // Si le choix correspond à un retour au début ou à un lien de mort,
        // restaurer les PV/Mana du héros au maximum.
        $shouldRestore = false;
        if ((int)$next === 2) $shouldRestore = true; // retour au début
        if (stripos($desc, 'mort') !== false) $shouldRestore = true; // lien de mort

        if ($shouldRestore) {
            $hero = Hero::loadById($_SESSION['hero_id']);
            if ($hero) {
                $hero->pv = $hero->pv_max;
                $hero->mana = $hero->mana_max;
                $hero->save();
            }
        }

        $this->show((int)$next);
    }

    public function getChapter(int $id): ?Chapter {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM Chapter WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $stmt2 = $db->prepare("SELECT * FROM Links WHERE chapter_id = ?");
        $stmt2->execute([$id]);
        $links = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return new Chapter(
            (int)$row['id'],
            $row['title'],
            $row['description'],
            $row['image'],
            $links
        );
    }

    public function index(): void { 
        // If an id is provided via GET, show that chapter, otherwise default to chapter 2
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 2;
        $this->show($id);
    }

    public function quit(): void {
        if (!isset($_POST['chapter_id'])) {
            header("Location: /DungeonXplorer/chapter");
            exit;
        }

        if (!isset($_SESSION['hero_id'])) die("Erreur : héros non sélectionné");

        $chapterId = (int)$_POST['chapter_id'];
        $db = getDB();

        // Récupère l'aventure en cours pour ce héros
        $stmt = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL");
        $stmt->execute([$_SESSION['hero_id']]);
        $adventureId = $stmt->fetchColumn();

        if (!$adventureId) {
            // Pas d'aventure en cours -> redirige vers l'accueil
            header("Location: /DungeonXplorer/home");
            exit;
        }

        // Met à jour la position courante de l'aventure
        $stmt2 = $db->prepare("UPDATE Adventure SET current_chapter_id = ? WHERE id = ?");
        $stmt2->execute([$chapterId, $adventureId]);

        // Enregistre un point de progression
        $stmt3 = $db->prepare("INSERT INTO Adventure_Progress (adventure_id, chapter_id, status) VALUES (?, ?, 'Saved')");
        $stmt3->execute([$adventureId, $chapterId]);

        // Si le formulaire fournit un état de combat, le sauvegarder en session pour reprise
        $hero_pv = isset($_POST['hero_pv']) ? (int)$_POST['hero_pv'] : null;
        $hero_mana = isset($_POST['hero_mana']) ? (int)$_POST['hero_mana'] : null;
        $monster_id = isset($_POST['monster_id']) ? (int)$_POST['monster_id'] : null;
        $monster_pv = isset($_POST['monster_pv']) ? (int)$_POST['monster_pv'] : null;
        $hero_turn = isset($_POST['hero_turn']) ? (int)$_POST['hero_turn'] : null;

        // Si des valeurs de héros ont été envoyées (quitte en plein combat),
        // on les persiste dans la fiche du héros afin que l'aventure et le
        // compte reflètent l'état actuel (pv/mana perdus).
        if ($hero_pv !== null || $hero_mana !== null) {
            $hero = Hero::loadById($_SESSION['hero_id']);
            if ($hero) {
                // Clamp values
                if ($hero_pv !== null) {
                    $hero->pv = max(0, min((int)$hero_pv, (int)$hero->pv_max));
                }
                if ($hero_mana !== null) {
                    $hero->mana = max(0, min((int)$hero_mana, (int)$hero->mana_max));
                }
                $hero->save();
            }
        }

        if ($monster_id !== null || $hero_pv !== null) {
            if (!isset($_SESSION['combat_snapshot'])) $_SESSION['combat_snapshot'] = [];
            $_SESSION['combat_snapshot'][$adventureId] = [
                'chapter_id' => $chapterId,
                'monster_id' => $monster_id,
                'monster_pv' => $monster_pv,
                'hero_pv' => $hero_pv,
                'hero_mana' => $hero_mana,
                'hero_turn' => $hero_turn ? 1 : 0,
                'saved_at' => time()
            ];
        }

        header("Location: /DungeonXplorer/adventure?id=" . $adventureId);
        exit;
    }

    public function resume(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Try to get adventure id from query, otherwise from current open adventure
        $adventureId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$adventureId && isset($_SESSION['hero_id'])) {
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL");
            $stmt->execute([$_SESSION['hero_id']]);
            $adventureId = $stmt->fetchColumn();
        }

        if (!$adventureId) {
            header("Location: /DungeonXplorer/adventure");
            exit;
        }

        // If we have a combat snapshot for this adventure, redirect to the saved chapter
        if (isset($_SESSION['combat_snapshot']) && isset($_SESSION['combat_snapshot'][$adventureId])) {
            $snap = $_SESSION['combat_snapshot'][$adventureId];
            if (isset($snap['chapter_id']) && (int)$snap['chapter_id'] > 0) {
                header("Location: /DungeonXplorer/chapter?id=" . (int)$snap['chapter_id']);
                exit;
            }
        }

        // Otherwise redirect to the adventure's current_chapter_id (so "Continuer" works for any quit)
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT current_chapter_id FROM Adventure WHERE id = ? LIMIT 1");
            $stmt->execute([$adventureId]);
            $current = $stmt->fetchColumn();
            if ($current && (int)$current > 0) {
                header("Location: /DungeonXplorer/chapter?id=" . (int)$current);
                exit;
            }
        } catch (Exception $e) {
            // ignore and fallback
        }

        // Fallback: open the adventure page
        header("Location: /DungeonXplorer/adventure?id=" . $adventureId);
        exit;
    }
}
