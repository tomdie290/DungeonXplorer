<?php
// Traiter la connexion avant d'envoyer du HTML (évite les problèmes de headers)
session_start();
try {
    require_once 'core/Database.php';
    $db = getDB();
}
catch (Exception $e) {
    die('Une erreur est survenue. Veuillez réessayer plus tard.' . $e->getMessage());
}

if(isset($_POST['username']) && isset($_POST['password'])) {

    $q = $db -> prepare("SELECT * FROM Account WHERE username = :username");
    $q -> execute(['username' => $_POST['username']]);
    $user = $q -> fetch();
    if($user && password_verify($_POST['password'], $user['password_hash'])) {
        // Login successful
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: account");
        exit();
    } else {
        $login_error = 'Nom d\'utilisateur ou mot de passe incorrect.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <?php require_once 'head.php'; ?>
        <title>Se connecter - DungeonXplorer</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>
    <body>

        <div class="login-container">
        <img class="mb d-block mx-auto" src="img/LogoVide.png" alt="logo application" width="400" height="400">
        <h1 class="login-title mb-4">Se connecter</h1>
        <form method="post" class="d-flex flex-column align-items-center gap-3">
            
            <div class="input-group w-100">
                <input type="text"
                       class="form-control background-secondaire texte-principal"
                       placeholder="Nom d'utilisateur"
                       aria-label="Username"
                       name="username"
                       required>
            </div>

            <div class="input-group w-100">
                <input type="password"
                       class="form-control background-secondaire texte-principal"
                       placeholder="Mot de passe"
                       aria-label="Password"
                       name="password"
                       required>
            </div>

            <input type="submit" value="Se connecter" class="btn btn-primary mt-2 w-50">

            <a href="home" class="back-btn mt-2">Retour</a>
        </form>
        <?php if(!empty($login_error)): ?>
            <div class="alert alert-danger mt-3 text-center">
                <?= htmlspecialchars($login_error) ?>
            </div>
        <?php endif; ?>

    </body>
</html>