<?php
require_once 'core/Database.php';

if (!isset($_SESSION['id'])) {
    header("Location: login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountId = $_POST['account_id'];
    $newPassword = $_POST['new_password'];

    // Vérifiez que l'utilisateur modifie uniquement son propre compte
    if ($accountId != $_SESSION['id']) {
        die("Action non autorisée.");
    }

    // Hachez le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Mettez à jour le mot de passe dans la base de données
    $db = getDB();
    $sql = "UPDATE Account SET password_hash = :password_hash WHERE id = :account_id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'password_hash' => $hashedPassword,
        'account_id' => $accountId
    ]);

    echo "Mot de passe mis à jour avec succès.";
    header("Location: profil");
    exit;
}
?>