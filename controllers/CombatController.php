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
