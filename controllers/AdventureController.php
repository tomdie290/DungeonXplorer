<?php
require_once 'core/Database.php';
require_once 'models/Hero.php';

class AdventureController
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id'])) {
            header("Location: /DungeonXplorer/login");
            exit;
        }

        $this->db = getDB();
    }

    /**
     * Page start_adventure
     */
    public function start()
    {
        $accountId = $_SESSION['id'];

        // 🔎 Récupérer le héros
        if (!isset($_GET['hero'])) {
            die("Aucun héros sélectionné");
        }

        $heroId = (int)$_GET['hero'];

        $stmt = $this->db->prepare(
            "SELECT * FROM Hero WHERE id = ? AND account_id = ?"
        );
        $stmt->execute([$heroId, $accountId]);
        $hero = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hero) {
            die("Ce héros ne vous appartient pas");
        }

        // ⭐ Héros actif en session
        $_SESSION['hero_id'] = $heroId;

        // 🔁 Vérifier aventure existante
        $stmt = $this->db->prepare(
            "SELECT id FROM Adventure WHERE hero_id = ? AND end_date IS NULL"
        );
        $stmt->execute([$heroId]);
        $existingAdventureId = $stmt->fetchColumn();

        // ▶️ Clic sur "Commencer l’aventure"
        if (isset($_GET['adventure'])) {

            // 👉 Si aventure déjà en cours → REDIRECTION
            if ($existingAdventureId) {
                header("Location: /DungeonXplorer/adventure?id=" . $existingAdventureId);
                exit;
            }

            $chapterId = (int)$_GET['adventure'];

            // Créer aventure
            $stmt = $this->db->prepare(
                "INSERT INTO Adventure (hero_id, current_chapter_id)
                 VALUES (?, ?)"
            );
            $stmt->execute([$heroId, $chapterId]);

            $adventureId = $this->db->lastInsertId();

            header("Location: /DungeonXplorer/adventure?id=" . $adventureId);
            exit;
        }

        // 📚 Liste des aventures
        $stmt = $this->db->query(
            "SELECT id, title, description, image FROM Chapter WHERE id = 1"
        );
        $adventures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../view/start_adventure.php';
    }

    /**
     * Page /adventure?id=...
     */
    public function index()
    {
        if (!isset($_GET['id'])) {
            die("Aucune aventure sélectionnée");
        }

        $adventureId = (int)$_GET['id'];
        $accountId = $_SESSION['id'];

        $stmt = $this->db->prepare("
            SELECT a.*, h.name AS hero_name
            FROM Adventure a
            JOIN Hero h ON a.hero_id = h.id
            WHERE a.id = ? AND h.account_id = ?
        ");
        $stmt->execute([$adventureId, $accountId]);
        $adventure = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adventure) {
            die("Aventure introuvable");
        }

        require __DIR__ . '/../view/adventure.php';
    }
}
