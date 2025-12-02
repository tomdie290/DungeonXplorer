<?php

abstract class Monster
{
    protected string $name;
    protected int $pv;
    protected int $mana;
    protected int $strength;
    protected int $initiative;
    protected string $attackText;
    protected int $xpReward;
    protected array $treasure;
    protected ?string $image;

    public function __construct(
        string $name,
        int $pv,
        int $mana,
        int $strength,
        int $initiative,
        string $attackText,
        int $xpReward,
        array $treasure = [],
        ?string $image = null
    ) {
        $this->name = $name;
        $this->pv = $pv;
        $this->mana = $mana;
        $this->strength = $strength;
        $this->initiative = $initiative;
        $this->attackText = $attackText;
        $this->xpReward = $xpReward;
        $this->treasure = $treasure;
        $this->image = $image;
    }

    abstract public function attack(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function getHealth(): int
    {
        return $this->pv;
    }

    public function getMana(): int
    {
        return $this->mana;
    }

    public function getExperienceValue(): int
    {
        return $this->xpReward;
    }

    public function getTreasure(): array
    {
        return $this->treasure;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function isAlive(): bool
    {
        return $this->pv > 0;
    }

    public function takeDamage(int $dmg): void
    {
        $this->pv -= $dmg;
        if ($this->pv < 0) $this->pv = 0;
    }
}
?>