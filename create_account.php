<!DOCTYPE html>
<html lang="en">
    <head>
        <?php require_once 'head.php'; ?>
        <title>Créer un compte - DungeonXplorer</title>
    </head>
    <body>
        <h1 class="pirata-one-regular texte-principal">DungeonXplorer</h1>
        <h2 class="pirata-one-regular texte-principal">Créer un compte</h2>
        <form action="create_account.php" method="post" class="d-flex justify-content-center align-items-center flex-column gap-2">
        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="text" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Username" aria-label="Username" aria-describedby="addon-wrapping" name="Username" required>
        </div>

        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="password" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Mot de passe" aria-label="Password" aria-describedby="addon-wrapping" name="Password" required>
        </div>

        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="password" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Confirmer le mot de passe" aria-label="PasswordConfirm" aria-describedby="addon-wrapping" name="PasswordConfirm" required>
        </div>

        <input type="submit" value="S'inscrire" class="btn btn-primary mt-3">
    </form>
        <?php
    try {
        require_once 'connexion.php';
    }
    catch (Exception $e) {
        die('Une erreur est survenue. Veuillez réessayer plus tard.' . $e->getMessage());
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username = $_POST['Username'];
        $password = $_POST['Password'];
        $passwordconfirm = $_POST['PasswordConfirm'];

        if($password !== $passwordconfirm) {
            echo "<p class='text-danger'>Les mots de passe ne correspondent pas.</p>"; // à remplacer par un truc boostrap
            exit;
        }
        else{
            global $conn;
            $q = $conn->prepare("INSERT INTO account (username, password_hash) VALUES (:username, :password)");
            $q->execute([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $_SESSION['username'] = $username;
            $_SESSION['userid']= null;

            $q = $db -> prepare("SELECT user_id FROM user WHERE user_name = :username");
            $q->execute(['username' => $username]);
            $user = $q->fetch();

            $_SESSION['userid'] = $user['user_id'];

            echo "<p class='success'>Inscription réussie pour l'utilisateur : $username</p>";
        }
    }
    ?>
    </body>
</html>