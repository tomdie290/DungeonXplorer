<?php
require_once 'core/Database.php';

class AdventureController
{
    public function newAdventure($hero)
    {
        $db = getDB();

        $sql = "SELECT * FROM Adventure WHERE hero_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hero]);

        if ($stmt->rowCount() > 0) {
            echo "Il y as deja une aventure en cours avec ce hero";
        } else {
            $sql = "INSERT INTO Adventure (hero_id) VALUES (?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$hero]);

            $sql = "UPDATE Account SET current_hero = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$hero, $_SESSION['id']]);

            header("Location: chapter");
        }
    }
}