<?php

require_once 'core/Database.php';

$db = getDB();
$q = $db->prepare("SELECT account.admin FROM Account WHERE id = :user_id");
$q->execute([
    'user_id' => $_SESSION['id']
]);
$isAdmin = $q->fetchColumn();
if (!$isAdmin) {
    header("Location: /DungeonXplorer/account");
    exit();
}



?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once 'head.php'; ?>
    <title>Mon compte - DungeonXplorer</title>
</head>

<body>
    <?php require_once 'navbar.php'; ?>
    <h2 class="login-title mt-5 mb-4">Administration</h2>
    <div class="container mb-5">
        <div class="list-group background-secondaire texte-principal">
            <a href="/manage_chapters" class="list-group-item list-group-item-action background-secondaire texte-principal">
                Gérer les chapitres
            </a>
            <a href="/manage_monsters" class="list-group-item list-group-item-action background-secondaire texte-principal">
                Gérer les monstres
            </a>
            <a href="/manage_images" class="list-group-item list-group-item-action background-secondaire texte-principal">
                Gérer les images
            </a>
            <a href="/manage_accounts" class="list-group-item list-group-item-action background-secondaire texte-principal">
                Gérer les comptes utilisateur
            </a>
        </div>
    </div>
</body>

</html>