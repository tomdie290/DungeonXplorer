<?php
require_once 'core/Database.php';

class Hero
{
    public int $id;
    public string $name;
    public string $class;

    public int $pv;
    public int $pv_max;

    public int $mana;
    public int $mana_max;

    public int $strength;
    public int $initiative;

    public int $armor_bonus;
    public int $weapon_bonus;

    public int $xp;
    public int $level;

    public string $image;

    public static function loadById(int $id): ?Hero
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT H.*, C.name AS class_name, C.base_pv, C.base_mana
            FROM Hero H
            JOIN Class C ON H.class_id = C.id
            WHERE H.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return self::fromArray($row);
    }

    public static function fromArray(array $data): Hero
    {
        $hero = new Hero();

        $hero->id = (int)$data['id'];
        $hero->name = $data['name'];
        $hero->class = $data['class_name'] ?? '';

        $hero->pv = (int)$data['pv'];
        $hero->pv_max = (int)$data['base_pv'];

        $hero->mana = (int)$data['mana'];
        $hero->mana_max = (int)$data['base_mana'];

        $hero->strength = (int)$data['strength'];
        $hero->initiative = (int)$data['initiative'];

        $hero->armor_bonus = (int)($data['armor_bonus'] ?? 0);
        $hero->weapon_bonus = (int)($data['weapon_bonus'] ?? 0);

        $hero->xp = (int)($data['xp'] ?? 0);
        $hero->level = (int)($data['current_level'] ?? 1);

        $hero->image = $data['image'] ?? 'img/HeroDefault.png';

        return $hero;
    }

    public function save(): void
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE Hero SET pv = ?, mana = ? WHERE id = ?");
        $stmt->execute([$this->pv, $this->mana, $this->id]);
    }
}
