<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$monsters = $monsters ?? [];
?>

<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
    <meta charset="UTF-8">
    <title>Gestion des monstres</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style> .small-img{max-width:50px;height:50px;object-fit:cover;} </style>
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container mt-5 background-secondaire texte-principal">
    <h1 class="mb-4 texte-principal">Gestion des monstres</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="table-responsive mb-4">
        <table class="background-secondaire texte-principal table">
            <thead>
                <tr class="background-secondaire texte-principal">
                    <th class="background-secondaire texte-principal">ID</th>
                    <th class="background-secondaire texte-principal">Nom</th>
                    <th class="background-secondaire texte-principal">PV</th>
                    <th class="background-secondaire texte-principal">Mana</th>
                    <th class="background-secondaire texte-principal">Force</th>
                    <th class="background-secondaire texte-principal">Initiative</th>
                    <th class="background-secondaire texte-principal">XP</th>
                    <th class="background-secondaire texte-principal">Image</th>
                    <th class="background-secondaire texte-principal">Actions</th>
                </tr>
            </thead>
            <tbody class="texte-principal">
                <?php foreach ($monsters as $m): ?>
                    <tr>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['id']); ?></td>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['name']); ?></td>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['pv']); ?></td>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['mana']); ?></td>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['strength']); ?></td>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['initiative']); ?></td>
                        <td class="background-secondaire texte-principal"><?php echo htmlspecialchars($m['xp_reward']); ?></td>
                        <td class="background-secondaire texte-principal">
                            <?php if (!empty($m['image'])): ?>
                                <?php
                                    // normalize stored image path to absolute /DungeonXplorer/... if needed
                                    $mi = $m['image'];
                                    if (!str_starts_with($mi, '/')) $mi = '/DungeonXplorer/' . ltrim($mi, '/');
                                ?>
                                <img src="<?php echo htmlspecialchars($mi); ?>" alt="<?php echo htmlspecialchars($m['name']); ?>" class="small-img">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="background-secondaire texte-principal">
                            <a href="/DungeonXplorer/manage_monsters/edit?id=<?php echo urlencode($m['id']); ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <form method="POST" action="/DungeonXplorer/manage_monsters/delete" onsubmit="return confirm('Supprimer <?php echo addslashes(htmlspecialchars($m['name'])); ?> ?');" style="display:inline-block; margin:0 0 0 .25rem;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($m['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card p-4 mb-4 background-secondaire texte-principal">
        <h3 class="mb-3 texte-principal">Ajouter un monstre</h3>
        <form method="POST" action="/DungeonXplorer/manage_monsters/store" class="d-flex flex-column gap-2 background-secondaire texte-principal">
            <div class="row g-2 background-secondaire texte-principal">
                <div class="col-md-4 background-secondaire texte-principal">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-2 background-secondaire texte-principal">
                    <label class="form-label">PV</label>
                    <input type="number" name="pv" class="form-control" required min="1">
                </div>
                <div class="col-md-2 background-secondaire texte-principal">
                    <label class="form-label">Mana</label>
                    <input type="number" name="mana" class="form-control" min="0" value="0">
                </div>
                <div class="col-md-2 background-secondaire texte-principal">
                    <label class="form-label">Force</label>
                    <input type="number" name="strength" class="form-control" required min="1">
                </div>
                <div class="col-md-2 background-secondaire texte-principal">
                    <label class="form-label">Initiative</label>
                    <input type="number" name="initiative" class="form-control" required min="1">
                </div>
            </div>

            <div class="row g-2 mt-2 background-secondaire texte-principal">
                <div class="col-md-8">
                    <label class="form-label">Texte d'attaque</label>
                    <input type="text" name="attack_text" class="form-control" placeholder="Ex: Le monstre attaque!">
                </div>
                <div class="col-md-4">
                    <label class="form-label">XP récompense</label>
                    <input type="number" name="xp_reward" class="form-control" required min="0" value="0">
                </div>
            </div>

            <div class="input-group flex-column align-items-start mt-2">
                <label class="input-group-text mb-2">Image</label>
                <?php
                $imageOptions = [];
                $imgDir = __DIR__ . '/../img';
                if (is_dir($imgDir)) {
                    $files = glob($imgDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                    foreach ($files as $f) {
                        $imageOptions[] = basename($f);
                    }
                }
                ?>
                <select name="image_path" class="form-select mt-2" id="imageSelector">
                    <option value="">-- Aucune image --</option>
                    <?php foreach ($imageOptions as $opt): ?>
                        <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="mt-3" id="imagePreview"></div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Ajouter le monstre</button>
            </div>
        </form>
    </div>

    <div class="mt-3">
        <a href="/DungeonXplorer/admin" class="btn btn-secondaire">Retour admin</a>
    </div>
</div>
 
<script>
document.getElementById('imageSelector').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    const filename = this.value;
    
    if (!filename) {
        preview.innerHTML = '';
        return;
    }
    
    const imgPath = '/DungeonXplorer/img/' + encodeURIComponent(filename);
    preview.innerHTML = '<img src="' + imgPath + '" alt="Aperçu" style="max-width:150px; height:auto; border:1px solid #ccc; padding:5px;">';
});
</script>

</body>
</html>
