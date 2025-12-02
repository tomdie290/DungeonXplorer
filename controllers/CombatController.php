<?php

class CombatController
{

    public function start($monster, int $chapterId)
    {
        if (!$monster instanceof Monster) {
            echo "Erreur : monstre invalide !";
            exit;
        }

        if (!$monster->isAlive()) {
            echo "Erreur : le monstre est déjà mort !";
            exit;
        }

        include 'view/combat.php';
    }
}
