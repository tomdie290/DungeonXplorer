<?php
require_once 'Monster.php';

class SanglierEnrage extends Monster
{
    public function __construct()
    {
        $treasure = ['gold' => 20, 'armor' => 'peau de sanglier'];
        parent::__construct(
            'Sanglier Enragé',   // nom
            50,                  // pv
            0,                   // mana
            12,                  // strength
            5,                   // initiative
            'Le sanglier charge violemment !', // attackText
            50,                  // xpReward
            $treasure,
            'img/monsters/boar.png'
        );
    }

    public function attack(): string
    {
        return $this->attackText;
    }
}
?>