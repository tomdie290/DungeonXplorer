<?php
require_once 'models/Chapter.php';
require_once 'core/Database.php';

class ChapterController
{
    private $chapters = [];

    public function __construct()
    {
        $db = getDB();

        $sql = "
            SELECT Chapter.id, Chapter.title, Chapter.description, Chapter.image,
                   Links.id AS link_id, Links.description AS link_text, Links.next_chapter_id
            FROM Chapter
            LEFT JOIN Links ON Links.chapter_id = Chapter.id
            ORDER BY Chapter.id ASC
        ";

        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chapters = [];

        foreach ($rows as $row) {
            $id = $row['id'];

            if (!isset($chapters[$id])) {
                $chapters[$id] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'image' => $row['image'],
                    'links' => []
                ];
            }

            if ($row['link_id']) {
                $chapters[$id]['links'][] = [
                    'id' => $row['link_id'],
                    'text' => $row['link_text'],
                    'chapter' => $row['next_chapter_id']
                ];
            }
        }

        foreach ($chapters as $c) {
            $this->chapters[$c['id']] = new Chapter(
                $c['id'], $c['title'], $c['description'], $c['image'], $c['links']
            );
        }
    }

    public function show($id)
    {
        $chapter = $this->getChapter($id);

        if (!$chapter) {
            require_once 'view/404.php';
            return;
        }

        if ($chapter->hasMonster()) {
            $monsterClass = $chapter->getMonsterType();
            if ($monsterClass) {
                require_once "models/{$monsterClass}.php";
                $monster = new $monsterClass();

                require_once "CombatController.php";
                $combat = new CombatController();
                $combat->start($monster, $chapter->getId());
                return;
            }
        }

        include 'view/chapter.php';
    }
public function getChapter($id)
{
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM Chapter WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return null;

    // Récupération des choix
    $stmt2 = $db->prepare("SELECT * FROM Links WHERE chapter_id = ?");
    $stmt2->execute([$id]);
    $rawChoices = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $choices = [];
    foreach ($rawChoices as $c) {
        $choices[] = [
            'id' => $c['id'],
            'text' => $c['description'],
            'chapter' => $c['next_chapter_id']
        ];
    }

    // Récupération du monstre
    $stmt3 = $db->prepare("
        SELECT M.name 
        FROM Encounter E
        JOIN Monster M ON E.monster_id = M.id
        WHERE E.chapter_id = ?
    ");
    $stmt3->execute([$id]);
    $monsterName = $stmt3->fetchColumn();

    $monsterClass = null;

    if ($monsterName) {
        // Normalisation du nom de classe
        $monsterClass = str_replace(
            [' ', 'é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ç'],
            ['','e','e','e','e','a','a','i','i','o','o','u','u','c'],
            $monsterName
        );
    }

    // ⬅️ Ici on renvoie bien l’image du chapitre depuis la base
    return new Chapter(
        (int)$row['id'],
        $row['title'],
        $row['description'],
        $row['image'],  // ✔️ l'image vient bien de la DB
        $choices,
        $monsterClass
    );
}



    public function index()
    {
        $this->show(2);
    }

    public function choice()
    {
        if (!isset($_POST['choice_id'])) {
            header("Location: /DungeonXplorer/chapter");
            exit;
        }

        $linkId = $_POST['choice_id'];
        $db = getDB();

        $stmt = $db->prepare("SELECT next_chapter_id FROM Links WHERE id = ?");
        $stmt->execute([$linkId]);
        $nextChapterId = $stmt->fetchColumn();

        if (!$nextChapterId) {
            echo "Erreur : ce choix ne mène à aucun chapitre.";
            exit;
        }

        $this->show((int)$nextChapterId);
    }
}
