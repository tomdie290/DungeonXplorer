<?php 
if (!isset($account)) { die("Erreur : aucune donnée de compte."); } 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Profil</title>
    <?php require 'head.php'; ?>
    <script>
        function togglePassword(id) {
            const field = document.getElementById(id);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</head>

<body class="texte-principal">

<div class="container mt-5">
    <h1 class="mb-4 pirata-one-regular">Profil du Compte</h1>

    <div class="card background-secondaire border border-2 border-white rounded-3 mb-5 p-4">
        <h2 class="text-center"><?= htmlspecialchars($account['username'] ?? '') ?></h2>
        <p class="text-center"><strong>Email :</strong> <?= htmlspecialchars($account['email'] ?? 'Non renseigné') ?></p>
        <p class="text-center"><strong>Créé le :</strong> <?= htmlspecialchars($account['creation_date'] ?? '') ?></p>

        <hr>

        <h3 class="text-center mb-3">Modifier votre profil</h3>
        <form method="POST" action="update_profile.php" class="d-flex flex-column gap-3 w-75 mx-auto">

            <div class="input-group">
                <span class="input-group-text">Nom d'utilisateur</span>
                <input type="text" name="username" class="form-control" 
                       value="<?= htmlspecialchars($account['username'] ?? '') ?>" required>
            </div>

            <div class="input-group">
                <span class="input-group-text">Nouveau mot de passe</span>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Laissez vide pour ne pas changer">
                <button type="button" class="btn btn-secondary" onclick="togglePassword('new_password')">Voir / Masquer</button>
            </div>

            <div class="input-group">
                <span class="input-group-text">Confirmer mot de passe</span>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirmez le mot de passe">
                <button type="button" class="btn btn-secondary" onclick="togglePassword('confirm_password')">Voir / Masquer</button>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>

    <h2 class="mb-3 pirata-one-regular">Vos Héros</h2>

    <?php if (empty($heroes)): ?>
        <p class="text-center">Aucun héros créé.</p>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($heroes as $hero): ?>
            <div class="col-md-4">
                <div class="hero-card">

                    <div class="hero-image-wrapper">
                        <img src="<?= htmlspecialchars($hero['image'] ?? 'assets/img/default.png') ?>" 
                             alt="Hero Image">
                    </div>

                    <h3><?= htmlspecialchars($hero['name'] ?? 'Inconnu') ?></h3>
                    <p>Classe : <strong><?= htmlspecialchars($hero['class_name'] ?? "Inconnue") ?></strong></p>
                    <p>Niveau : <strong><?= $hero['current_level'] ?? 1 ?></strong></p>
                    <p>XP : <?= $hero['xp'] ?? 0 ?></p>

                    <hr>

                    <p><strong>PV :</strong> <?= $hero['pv'] ?? 0 ?></p>
                    <p><strong>Mana :</strong> <?= $hero['mana'] ?? 0 ?></p>
                    <p><strong>Force :</strong> <?= $hero['strength'] ?? 0 ?></p>
                    <p><strong>Initiative :</strong> <?= $hero['initiative'] ?? 0 ?></p>

                    <hr>

                    <p><strong>Chapitre actuel :</strong><br>
                        <?= htmlspecialchars($hero['chapter_title'] ?? "Pas d’aventure en cours") ?>
                    </p>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="account" class="btn btn-danger">Retour</a>
    </div>

    <div class="text-center mt-4">
        <a href="logout" class="btn btn-danger">Se déconnecter</a>
    </div>
</div>

</body>
</html>
