<?php
require_once 'core/Database.php';

class AdventureController
{
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        if (!isset($_SESSION['id'])) {
            header("Location: login");
            exit;
        }

        $this->db = getDB();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            header("Location: home");
        }
        if (!isset($_GET['id'])) {
            die("Aucune aventure sélectionnée.");
        }

        $adventureId = intval($_GET['id']);
        $accountId = $_SESSION['id'];

        $sql = "
            SELECT 
                a.*,
                h.name AS hero_name,
                h.image AS hero_image,
                h.pv, h.mana, h.strength, h.initiative,
                h.current_level, h.xp,
                c.name AS class_name
            FROM Adventure a
            JOIN Hero h ON a.hero_id = h.id
            LEFT JOIN Class c ON h.class_id = c.id
            WHERE a.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adventureId]);
        $adventure = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adventure) {
            die("Aventure introuvable");
        }

        $stmt = $this->db->prepare("SELECT id FROM Hero WHERE id = ? AND account_id = ?");
        $stmt->execute([$adventure['hero_id'], $accountId]);
        if (!$stmt->fetch()) {
            die("Cette aventure ne vous appartient pas.");
        }

        require 'view/adventure.php';
    }

    public function start()
    {
        if (!isset($_GET['hero'])) {
            die("Aucun héros sélectionné.");
        }

        $accountId = $_SESSION['id'];
        $heroId = intval($_GET['hero']);

        $stmt = $this->db->prepare("SELECT * FROM Hero WHERE id = ? AND account_id = ?");
        $stmt->execute([$heroId, $accountId]);
        $hero = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hero) {
            die("Ce héros ne vous appartient pas.");
        }

        if (isset($_GET['adventure'])) {

            $advId = intval($_GET['adventure']);

            $stmt = $this->db->prepare("SELECT id FROM Chapter WHERE id = ?");
            $stmt->execute([$advId]);
            if (!$stmt->fetch()) {
                die("Aventure inconnue.");
            }

            $stmt = $this->db->prepare("SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL");
            $stmt->execute([$heroId]);
            if ($stmt->fetch()) {
                die("Ce héros est déjà en aventure.");
            }

            $stmt = $this->db->prepare("INSERT INTO Adventure (hero_id, current_chapter_id) VALUES (?, ?)");
            $stmt->execute([$heroId, $advId]);

            $adventureId = $this->db->lastInsertId();

            $stmt = $this->db->prepare("UPDATE Account SET current_hero = ? WHERE id = ?");
            $stmt->execute([$heroId, $accountId]);

            header("Location: adventure?id=" . $adventureId);
            exit;
        }

        $sql = "
            SELECT id, title, description, image
            FROM Chapter
            WHERE id IN (1)
        ";

        $adventures = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        require 'view/start_adventure.php';
    }
    private function getEncounterForChapter(int $chapterId) 
    {
        $db = getDB();

        $sql = "SELECT m.*
                FROM Encounter e
                JOIN Monster m ON e.monster_id = m.id
                WHERE e.chapter_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$chapterId]);
        $monster = $stmt->fetch(PDO::FETCH_ASSOC);

        return $monster ? new Monster($monster) : null;
    }
}
