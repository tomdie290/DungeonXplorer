<form action="/DungeonXplorer/admin/chapter/store" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
  <label>Titre</label>
  <input type="text" name="title" required>
  <label>Description</label>
  <textarea name="description" required></textarea>
  <label>Image</label>
  <input type="file" name="image" accept="image/*">
  <button type="submit">Créer le chapitre</button>
</form>