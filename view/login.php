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

    $q = $db -> prepare("SELECT * FROM account WHERE username = :username");
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
    </head>
    <body>
        <h1 class="pirata-one-regular texte-principal">DungeonXplorer</h1>
        <h2 class="pirata-one-regular texte-principal">Se connecter</h2>
        <form  method="post" class="d-flex justify-content-center align-items-center flex-column gap-2">
        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="text" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Username" aria-label="Username" aria-describedby="addon-wrapping" name="username" required>
        </div>

        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="password" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Mot de passe" aria-label="Password" aria-describedby="addon-wrapping" name="password" required>
        </div>

        <input type="submit" value="Se connecter" class="btn btn-primary mt-3">

        <a href="home" class="btn-primary"
           style="padding: 12px 25px; text-decoration: none; color: #E5E5E5; border: 2px solid #C4975E; border-radius: 8px;">
            retour
        </a>
    </form>
        <?php if(!empty($login_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>

    </body>
</html>