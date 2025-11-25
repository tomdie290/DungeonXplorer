<?php
session_start();
    try {
        require_once 'core/Database.php';
    }
    catch (Exception $e) {
        die('Une erreur est survenue. Veuillez réessayer plus tard.' . $e->getMessage());
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username = $_POST['Username'];
        $password = $_POST['Password'];
        $passwordconfirm = $_POST['PasswordConfirm'];

        if($password !== $passwordconfirm) {
            echo "<p class='text-danger'>Les mots de passe ne correspondent pas.</p>";
            exit;
        }
        else{

            $db = getDB();
            $q = $db -> prepare("INSERT INTO Account (username, password_hash) VALUES (:username, :password)");
            $q->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $_SESSION['username'] = $username;
            $_SESSION['id']= null;

            $q = $db -> prepare("SELECT id FROM Account WHERE username = :username");
            $q->execute(['username' => $username]);
            $user = $q->fetch();

            $_SESSION['id'] = $user['id'];
            
            echo "<p class='success'>Inscription réussie pour l'utilisateur : $username</p>";
            header("Location: home");
        }

    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <title>Créer un compte - DungeonXplorer</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<div class="login-container">
    <img class="mb-4 d-block mx-auto" src="img/LogoVide.png" alt="logo application" width="400" height="400">
    <h1 class="login-title mb-4">Créer un compte</h1>
    <form method="post" class="d-flex flex-column align-items-center gap-3">
        
        <div class="input-group w-100">
            <input type="text"
                   class="form-control background-secondaire texte-principal"
                   placeholder="Nom d'utilisateur"
                   aria-label="Username"
                   name="Username"
                   required>
        </div>

        <div class="input-group w-100">
            <input type="password"
                   class="form-control background-secondaire texte-principal"
                   placeholder="Mot de passe"
                   aria-label="Password"
                   name="Password"
                   required>
        </div>

        <div class="input-group w-100">
            <input type="password"
                   class="form-control background-secondaire texte-principal"
                   placeholder="Confirmer le mot de passe"
                   aria-label="PasswordConfirm"
                   name="PasswordConfirm"
                   required>
        </div>

        <input type="submit" value="S'inscrire" class="btn btn-primary mt-2 w-50">
        <a href="home" class="back-btn mt-2">Retour</a>
    </form>
    <?php if (!empty($register_error)): ?>
        <div class="alert alert-danger mt-3 text-center">
            <?= htmlspecialchars($register_error) ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>