<?php
session_start();
try{
require 'core/Database.php';
$db = getDB(); 
$stmt = $db->prepare("SELECT id, name FROM Class");
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur lors de la récupération des classes : " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php require 'view/head.php'; 
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnalisez votre héros</title>
</head>
<body>
    <h1>Personnalisez votre héros</h1>
    <form action="process_hero.php" method="POST">
        <label for="name">Nom du héros:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="class">Classe:</label>
        <select id="class" name="class" required>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option> 
            <?php endforeach; ?>
        </select><br>

        <input type="submit" value="Créer le héros">
    </form>
</body>
</html>