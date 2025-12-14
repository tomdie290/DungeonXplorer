<?php
require_once 'models/Hero.php';
require_once 'models/Monster.php';

class CombatController
{
    public function start(Monster $monster, int $chapterId): void
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

        require __DIR__ . '/../view/combat.php';
    }
}
