<?php
class Treasure
{
    public $id;
    public $name;
    public $value;
    public $description;
    public $image;

    public static function all()
    {
        require_once __DIR__ . '/../core/Database.php';
        $db = getDB();
        $q = $db->query('SELECT * FROM Treasure ORDER BY id DESC');
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        require_once __DIR__ . '/../core/Database.php';
        $db = getDB();
        $q = $db->prepare('SELECT * FROM Treasure WHERE id = :id');
        $q->execute(['id' => $id]);
        return $q->fetch(PDO::FETCH_ASSOC);
    }
}
