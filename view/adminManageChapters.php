<!doctype html>
<html lang="fr">
<head>
    <?php require_once 'head.php'; ?>
    <meta charset="UTF-8">
    <title>Gestion des chapitres</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>
<?php require_once 'navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4 pirata-one-regular">Gestion des chapitres</h1>

    <div class="card hero-card background-secondaire rounded-3 mb-5 p-4">
        <h2 class="text-center mb-4">Ajouter un nouveau chapitre</h2>
        <form method="POST" action="/DungeonXplorer/admin/manage_chapters/store" class="d-flex flex-column gap-3 w-75 mx-auto">
            <div class="input-group">
                <span class="input-group-text">Titre du chapitre</span>
                <input type="text" name="title" class="form-control" required>
            </div>
            
            <div class="input-group">
                <span class="input-group-text">Contenu du chapitre</span>
                <textarea name="content" class="form-control" rows="5" required></textarea>
            </div>

            <div class="input-group flex-column align-items-start">
                <label class="input-group-text mb-2">Sélecteur d'images</label>
                <?php
                // Récupère les images du dossier img/ et uploads/chapters/ pour proposer un choix
                $imageOptions = [];
                $imgDir = __DIR__ . '/../img';
                $uploadDir = __DIR__ . '/../uploads/chapters';
                if (is_dir($imgDir)) {
                    $files = glob($imgDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    foreach ($files as $f) {
                        $imageOptions[] = 'img/' . basename($f);
                    }
                }
                if (is_dir($uploadDir)) {
                    $files = glob($uploadDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    foreach ($files as $f) {
                        $imageOptions[] = 'uploads/chapters/' . basename($f);
                    }
                }
                ?>

                <select id="imageSelector" name="image_path" class="form-select mt-2 w-100">
                    <option value="">-- Aucune image sélectionnée --</option>
                    <?php foreach ($imageOptions as $img): ?>
                        <option value="/<?php echo 'DungeonXplorer/img/' . $img; ?>"><?php echo basename($img); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="mt-3 text-center w-100">
                    <img id="imagePreview" src="" alt="Aperçu" style="max-width:240px; display:none; border:1px solid #ccc; padding:6px;" />
                </div>

                <script>
                    (function(){
                        const sel = document.getElementById('imageSelector');
                        const preview = document.getElementById('imagePreview');
                        sel.addEventListener('change', function(){
                            const v = this.value;
                            if(v){ preview.src = v; preview.style.display = 'block'; }
                            else { preview.src = ''; preview.style.display = 'none'; }
                        });
                    })();
                </script>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Ajouter le chapitre</button>
            </div>
        </form>
    </div>
</body>
</html>
