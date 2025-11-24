<!DOCTYPE html>
<html lang="en">
    <head>
        <?php require_once 'head.php'; ?>
        <title>Créer un compte - DungeonXplorer</title>
    </head>
    <body>
        <h1 class="pirata-one-regular texte-principal">DungeonXplorer</h1>
        <h2 class="pirata-one-regular texte-principal">Créer un compte</h2>
        <form action="register.php" method="post" class="d-flex justify-content-center align-items-center flex-column gap-2">
        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="text" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Username" aria-label="Username" aria-describedby="addon-wrapping">
        </div>

        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="password" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Mot de passe" aria-label="Password" aria-describedby="addon-wrapping">
        </div>

        <div class="input-group flex-nowrap w-50 mx-auto">
            <span class="input-group-text" id="addon-wrapping">@</span>
            <input type="password" class="form-control form-control-sm background-secondaire texte-principal" placeholder="Confirmer le mot de passe" aria-label="PasswordConfirm" aria-describedby="addon-wrapping">
        </div>

        <input type="submit" value="S'inscrire" class="btn btn-primary mt-3">
    </form>
    </body>
</html>