<?php

if (!isset($chapter)) {
    header('Location: /admin/manage_chapters');
    exit;
}
?>

<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Modifier le chapitre</title>
</head>

<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5">
    <h1 class="mb-4">Modifier le chapitre #<?php echo htmlspecialchars($chapter['id']); ?></h1>

    <div class="card hero-card background-secondaire rounded-3 mb-5 p-4">
        <form method="POST" action="/manage_chapters/update" class="d-flex flex-column gap-3 w-75 mx-auto">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($chapter['id']); ?>">

            <div class="input-group">
                <span class="input-group-text">Titre du chapitre</span>
                <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($chapter['title']); ?>">
            </div>

            <div class="input-group flex-column align-items-start mt-3">
                <label class="input-group-text mb-2">Choix de chapitre (jusqu'à 2)</label>
                <?php
                $links = $links ?? [];
                $allChapters = $allChapters ?? [];
                for ($ci = 0; $ci < 2; $ci++):
                    $link = $links[$ci] ?? null;
                    $selectedNext = $link['next_chapter_id'] ?? '';
                    $linkText = $link['description'] ?? '';
                ?>
                <div class="mb-2 w-100">
                    <div class="d-flex gap-2">
                        <select name="choice_next[]" class="form-select w-50">
                            <option value="">-- Aucun choix --</option>
                            <?php foreach ($allChapters as $ac): if ($ac['id'] == $chapter['id']) continue; ?>
                                <option value="<?php echo htmlspecialchars($ac['id']); ?>"<?php echo ($selectedNext !== null && (int)$selectedNext === (int)$ac['id']) ? ' selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ac['id'] . ' - ' . $ac['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="choice_text[]" class="form-control" placeholder="Texte du choix (optionnel)" value="<?php echo htmlspecialchars($linkText); ?>">
                    </div>
                </div>
                <?php endfor; ?>
            </div>

            <div class="input-group">
                <span class="input-group-text">Description du chapitre</span>
                <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($chapter['description']); ?></textarea>
            </div>

            <div class="input-group flex-column align-items-start">
                <label class="input-group-text mb-2">Sélecteur d'images</label>
                <?php
                $imageOptions = [];
                $imgDir = __DIR__ . '/../img';
                $uploadDir = __DIR__ . '/../uploads/chapters';
                if (is_dir($imgDir)) {
                    $files = glob($imgDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    foreach ($files as $f) {
                        $imageOptions[] = ['type' => 'img', 'file' => basename($f)];
                    }
                }
                if (is_dir($uploadDir)) {
                    $files = glob($uploadDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    foreach ($files as $f) {
                        $imageOptions[] = ['type' => 'uploads/chapters', 'file' => basename($f)];
                    }
                }
                ?>

                <select id="imageSelector" name="image_path" class="form-select mt-2 w-100">
                    <option value="">-- Aucune image sélectionnée --</option>
                    <?php foreach ($imageOptions as $opt):
                        $pathPrefix = $opt['type'] === 'img' ? 'img/' : 'uploads/chapters/';
                        $url = '/' . $pathPrefix . rawurlencode($opt['file']);
                        $fullStored = $chapter['image'] ?? '';
                        $isSelected = ($fullStored === $url) ? ' selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($url); ?>"<?php echo $isSelected; ?>><?php echo htmlspecialchars($opt['file']); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="mt-3 text-center w-100">
                    <img id="imagePreview" src="<?php echo htmlspecialchars($chapter['image'] ?? ''); ?>" alt="Aperçu" style="max-width:240px; <?php echo empty($chapter['image']) ? 'display:none;' : ''; ?> border:1px solid #ccc; padding:6px;" />
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
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="/manage_chapters" class="btn btn-secondary ms-2">Annuler</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
