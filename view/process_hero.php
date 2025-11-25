<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'nom_de_votre_base';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des données du formulaire
$name = $_POST['name'];
$class_id = (int)$_POST['class'];

// Récupération des valeurs par défaut de la classe
$stmt = $pdo->prepare("SELECT base_pv, base_mana, strength, initiative, max_items FROM classes WHERE id = :class_id");
$stmt->execute([':class_id' => $class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    die("Classe invalide.");
}

// Insertion du héros dans la base
$sql = "INSERT INTO heroes (name, class_id, pv, mana, strength, initiative, max_items) 
        VALUES (:name, :class_id, :pv, :mana, :strength, :initiative, :max_items)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':name' => $name,
    ':class_id' => $class_id,
    ':pv' => $class['base_pv'],
    ':mana' => $class['base_mana'],
    ':strength' => $class['strength'],
    ':initiative' => $class['initiative'],
    ':max_items' => $class['max_items'],
]);

echo "Héros créé avec succès !";
?>