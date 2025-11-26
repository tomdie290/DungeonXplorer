<?php

// controllers/ChapterController.php

require_once 'models/Chapter.php';
require_once 'core/Database.php';

class ChapterController
{
    private $chapters = [];

    public function __construct()
    {
        $db = getDB();

        $stmt = $db->query("SELECT id FROM Chapter ORDER BY id ASC");
        $chapters = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($chapters as $chapter) {

            $stmtChapter = $db->prepare("
        SELECT id, title, description, image
        FROM Chapter
        WHERE id = ?
    ");
            $stmtChapter->execute([$chapter]);
            $chapterRow = $stmtChapter->fetch(PDO::FETCH_ASSOC);

            $stmtLinks = $db->prepare("
        SELECT description AS text, next_chapter_id AS chapter
        FROM Links
        WHERE chapter_id = ?
    ");
            $stmtLinks->execute([$chapter]);
            $links = $stmtLinks->fetchAll(PDO::FETCH_ASSOC);

            $this->chapters[] = new Chapter(
                $chapterRow['id'],
                $chapterRow['title'],
                $chapterRow['description'],
                $chapterRow['image'],
                $links
            );
        }

    }

    public function show($id)
    {
        $chapter = $this->getChapter($id);

        if ($chapter) {
            include 'view/chapter.php'; // Charge la vue pour le chapitre
        } else {
            // Si le chapitre n'existe pas, redirige vers un chapitre par défaut ou affiche une erreur
            header('HTTP/1.0 404 Not Found');
            echo "Chapitre non trouvé!";
        }
    }

    public function getChapter($id)
    {
        foreach ($this->chapters as $chapter) {
            if ($chapter->getId() == $id) {
                return $chapter;
            }
        }
        return null;
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            header("Location: login");
        } else {

            $sql = "SELECT current_chapter_id AS id FROM Adventure 
                    JOIN Hero ON Hero.id = Adventure.hero_id
                    JOIN Account ON Account.id = Hero.account_id
                    WHERE Account.id = ? AND Account.current_hero = Hero.id AND Adventure.end_date IS NULL";
            $stmt = getDB()->prepare($sql);
            $stmt->execute([$_SESSION['id']]);
            $id = $stmt->fetchColumn();

            $this->show($id);
        }

    }
}