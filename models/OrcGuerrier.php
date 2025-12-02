<?php
require_once 'Monster.php';

class OrcGuerrier extends Monster
{
    public function __construct()
    {
        $treasure = ['gold' => 50, 'armor' => 'armure en cuir'];
        parent::__construct(
            'Orc Guerrier',      // nom
            70,                  // pv
            0,                   // mana
            15,                  // strength
            8,                   // initiative
            'L’orc abat sa massue !', // attackText
            120,                 // xpReward
            $treasure,
            'img/monsters/orc.png'
        );
    }

    public function attack(): string
    {
        return $this->attackText;
    }
}
?>