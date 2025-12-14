<?php
class Chapter
{
    private int $id;
    private string $title;
    private string $description;
    private string $image;
    private array $choices;
    private ?string $monsterType;

    public function __construct(int $id, string $title, string $description, string $image, array $choices = [], ?string $monsterType = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->choices = $choices;
        $this->monsterType = $monsterType;
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getImage(): string { return $this->image; }
    public function getChoices(): array { return $this->choices; }
    public function hasMonster(): bool { return $this->monsterType !== null; }
    public function getMonsterType(): ?string { return $this->monsterType; }

    public function addChoice(array $choice): void
    {
        $this->choices[] = $choice;
    }
}
?>