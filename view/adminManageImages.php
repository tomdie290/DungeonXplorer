<?php
if (!isset($images)) $images = [];
?>

<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Gestion des images</title>
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Gestion des images</h1>
    
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
        <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <div class="card mb-4 p-3 background-secondaire">
            <h4 class="texte-principal">Ajouter une image</h4>
            <p class="texte-principal">Avant d'ajouter une image, assurez-vous qu'elle porte un nom de fichier clair et non utilisé.</p>
        <form method="POST" action="/manage_images/upload" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
            <input type="file" class="texte-principal" name="image" accept="image/*" required>
            <button class="btn btn-primary" type="submit">Téléverser</button>
        </form>
    </div>

    <div class="card p-3 background-secondaire">
        <h4>Images dans le dossier `img/`</h4>
        <?php if (empty($images)): ?>
            <p>Aucune image trouvée.</p>
        <?php else: ?>
            <div class="row background-secondaire">
                <?php foreach ($images as $img): ?>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card p-2 text-center background-secondaire">
                            <img src="<?php echo htmlspecialchars('img/' . rawurlencode($img)); ?>" alt="<?php echo htmlspecialchars($img); ?>" style="max-width:100%; height:120px; object-fit:cover;">
                            <div class="mt-2 small text-truncate texte-principal"><?php echo htmlspecialchars($img); ?></div>
                            <div class="mt-2 d-flex justify-content-center gap-2">
                                <form method="POST" action="/manage_images/delete" onsubmit="return confirm('Supprimer <?php echo htmlspecialchars($img); ?> ?');">
                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($img); ?>">
                                    <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-3">
        <a href="/admin" class="btn btn-secondary">Retour admin</a>
    </div>
</div>
</body>
</html>
