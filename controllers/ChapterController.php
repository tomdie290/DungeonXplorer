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

        $chapter = $this->getChapter($id);
        if (!$chapter) { 
            require 'view/404.php'; 
            return; 
        }

        // Vérifie s’il y a un monstre dans ce chapitre
        $monster = Monster::loadByChapter($chapter->getId());
        if ($monster !== null) {
            $combat = new CombatController();
            $combat->start($monster, $chapter->getId());
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
        $stmt = $db->prepare("SELECT next_chapter_id FROM Links WHERE id = ?");
        $stmt->execute([$choiceId]);
        $next = $stmt->fetchColumn();

        if (!$next) die("Choix invalide");

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
        $this->show(2); 
    }
}
