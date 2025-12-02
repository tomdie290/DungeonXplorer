<?php

// models/Chapter.php

class Chapter
{
    private $id;
    private $title;
    private $description;
    private $image; 
    private $choices;
    private $monsterType;

    public function __construct($id, $title, $description, $image, $choices, ?string $monsterType = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image; 
        $this->choices = $choices;
        $this->monsterType = $monsterType;
    }

    public function getId()
    {   
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getImage()
    {
        return $this->image; 
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function hasMonster()
    {
        return $this->monsterType !== null;
    }

    public function getMonsterType()
    {
        return $this->monsterType;
    }

}
