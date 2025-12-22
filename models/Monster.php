<?php
require_once 'core/Database.php';

class Monster
{
    public int $id;
    public string $name;
    public int $pv;
    public int $mana;
    public int $strength;
    public string $image;

    // Charger un monstre selon un chapitre
    public static function loadByChapter(int $chapterId): ?Monster
    {
        $db = getDB();

        $stmt = $db->prepare("
            SELECT m.*
            FROM Monster m
            JOIN Encounter e ON e.monster_id = m.id
            WHERE e.chapter_id = ?
            LIMIT 1
        ");
        $stmt->execute([$chapterId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $monster = new Monster();
        $monster->id = $row['id'];
        $monster->name = $row['name'];
        $monster->pv = $row['pv'];
        $monster->mana = $row['mana'];
        $monster->strength = $row['strength'];
        $monster->image = $row['image'] ?? 'img/monster_default.png';

        return $monster;
    }

    public function getName(): string { return $this->name; }
    public function getImage(): string { return $this->image; }
    public function getHp(): int { return $this->pv; }
    public function getStrength(): int { return $this->strength; }
    public function getId(): int { return $this->id; }
}
?>