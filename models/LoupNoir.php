<?php
require_once 'Monster.php';

class LoupNoir extends Monster
{
    public function __construct()
    {
        $treasure = ['gold' => 30, 'armor' => 'fourrure sombre'];
        parent::__construct(
            'Loup Noir',         // nom
            40,                  // pv
            0,                   // mana
            8,                   // strength
            12,                  // initiative
            'Le loup bondit et tente de vous mordre !', // attackText
            60,                  // xpReward
            $treasure,
            'img/monsters/wolf.png'
        );
    }

    public function attack(): string
    {
        return $this->attackText;
    }
}
?>