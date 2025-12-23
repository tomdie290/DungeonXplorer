<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: home");
}

if (isset($_SESSION['id'])) {
    require_once __DIR__ . '/../core/Database.php';
    $db = getDB();

    $stmt = $db->prepare("SELECT username FROM Account WHERE id = ?");
    $stmt->execute([$_SESSION['id']]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $username = htmlspecialchars($row['username']);
    }
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$currentPage = end($segments);

if($currentPage === '') {
    $currentPage = 'home';
}
function isActive($page, $currentPage) {
    return $page === $currentPage ? ' active' : '';
}

function adapterPrefixeURLImage(){
  $ret = "";
  for($i = 0; $i < substr_count($_SERVER['REQUEST_URI'], '/') - 1; $i++){
    $ret .= "../";
  }
  return $ret;
}

?>
<nav class="navbar navbar-expand-lg navbar-dark site-navbar">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="account">
      <img src="<?php echo adapterPrefixeURLImage(); ?>img/Logo.png" alt="logo" class="navbar-brand-img" width="36" height="36">
      <span class="ms-2 brand-title">DungeonXplorer</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link<?= isActive('account', $currentPage) ?>" href="/account">Accueil</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActive('hero', $currentPage) ?>" href="/hero">Crée héros</a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActive('profil', $currentPage) ?>" href="/profil">Mon compte</a>
        </li>
        <li class="nav-item"><a class="nav-link" href="/deconnexion">Deconnexion</a></li>
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
          <li class="nav-item">
            <a class="nav-link<?= isActive('admin', $currentPage) ?>" href="/admin">Page Admin</a>
          </li>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['id'])): ?>
          <div class="dropdown" >
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle profile-dropdown" href="profil" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?php echo adapterPrefixeURLImage(); ?>img/profil.png" alt="Profil" class="profile-icon rounded-circle" width="32" height="32">
              <span class="ms-2 profile-name"><?= $username ?></span>
            </a>


          </div>
        <?php else: ?>
          <a href="login" class="btn btn-outline-light me-2">Se connecter</a>
          <a href="register" class="btn btn-primary">S'inscrire</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>