<?php
require_once 'core/Database.php';

class ProfilController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            header("Location: login");
        }

         $db = getDB();
        $accountId = $_SESSION['id'];

        $sqlAccount = "SELECT * FROM Account WHERE id = ?";
        $stmt = $db->prepare($sqlAccount);
        $stmt->execute([$accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        $sqlHeroes = "
            SELECT 
                h.id, h.name, h.image, h.xp, h.current_level,
                h.pv, h.mana, h.strength, h.initiative,
                c.name AS class_name,
                ch.title AS chapter_title
            FROM Hero h
            LEFT JOIN Class c ON h.class_id = c.id
            LEFT JOIN Adventure a ON a.hero_id = h.id AND a.end_date IS NULL
            LEFT JOIN Chapter ch ON a.current_chapter_id = ch.id
            WHERE h.account_id = ?
        ";
        $stmtHeroes = $db->prepare($sqlHeroes);
        $stmtHeroes->execute([$accountId]);
        $heroes = $stmtHeroes->fetchAll(PDO::FETCH_ASSOC);

        require_once 'view/profil.php';
    }
}