<?php
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$username = $_SESSION['username'] ?? 'Guest';

$sidebarLinks = [
    ['label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'fa-chart-line', 'match' => 'dashboard'],
    ['label' => 'Wydatki', 'href' => '/expenses', 'icon' => 'fa-wallet', 'match' => 'expenses'],
    ['label' => 'Statystyki', 'href' => '/statistics', 'icon' => 'fa-chart-simple', 'match' => 'statistics'],
    ['label' => 'Kategorie', 'href' => '/categories', 'icon' => 'fa-layer-group', 'match' => 'categories'],
    ['label' => 'Profil', 'href' => '/profile', 'icon' => 'fa-user', 'match' => 'profile'],
];

$mobileLinks = [
    ['label' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'fa-house', 'match' => 'dashboard'],
    ['label' => 'Wydatki', 'href' => '/expenses', 'icon' => 'fa-wallet', 'match' => 'expenses', 'exact' => true],
    ['label' => 'Dodaj', 'href' => '/expenses/create', 'icon' => 'fa-plus', 'match' => 'expenses/create', 'exact' => true],
    ['label' => 'Statystyki', 'href' => '/statistics', 'icon' => 'fa-chart-simple', 'match' => 'statistics'],
    ['label' => 'Profil', 'href' => '/profile', 'icon' => 'fa-user', 'match' => 'profile'],
];

$isActive = static function (array $link) use ($currentPath): bool {
    if (!empty($link['exact'])) {
        return $currentPath === $link['match'];
    }

    return $currentPath === $link['match'] || str_starts_with($currentPath, $link['match'].'/');
};

$currentLabel = 'Dashboard';
foreach ($sidebarLinks as $link) {
    if ($isActive($link)) {
        $currentLabel = $link['label'];
        break;
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/styles/main.css?v=statistics-charts-1" />
    <script src="https://kit.fontawesome.com/8fd9367667.js" crossorigin="anonymous"></script>
    <script src="/scripts/main.js?v=statistics-charts-1" defer></script>
    <title><?= $title ?? "App"; ?></title>
  </head>
  <body class="app-page <?= $bodyClass ?? ''; ?>">
    <div class="app-shell">
      <aside class="app-sidebar">
        <a class="sidebar-brand" href="/dashboard">
          <span class="app-brand-mark"><i class="fa-solid fa-wallet"></i></span>
          <span>
            <strong>Luminous</strong>
            <small>personal fintech</small>
          </span>
        </a>

        <nav class="sidebar-nav" aria-label="Primary navigation">
          <?php foreach ($sidebarLinks as $link): ?>
            <a class="sidebar-link <?= $isActive($link) ? 'active' : ''; ?>" href="<?= $link['href']; ?>">
              <i class="fa-solid <?= $link['icon']; ?>"></i>
              <span><?= $link['label']; ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="sidebar-user">
          <span class="user-avatar"><?= htmlspecialchars(strtoupper(substr($username, 0, 1))); ?></span>
          <span>
            <strong><?= htmlspecialchars($username); ?></strong>
            <small>Personal account</small>
          </span>
        </div>
      </aside>

      <div class="app-main">
        <header class="app-topbar">
          <form class="topbar-search" action="/expenses" method="GET">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="search" type="search" placeholder="Search assets..." />
          </form>

          <a class="topbar-link <?= $isActive(['match' => 'dashboard']) ? 'active' : ''; ?>" href="/dashboard"><?= htmlspecialchars(strtoupper($currentLabel)); ?></a>

          <div class="topbar-actions">
            <button type="button" aria-label="Notifications"><i class="fa-solid fa-bell"></i></button>
            <button type="button" aria-label="Settings"><i class="fa-solid fa-gear"></i></button>
          </div>
        </header>

        <main class="app-content">
          <?= $content ?>
        </main>
      </div>
    </div>

    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
      <?php foreach ($mobileLinks as $link): ?>
        <a class="<?= $isActive($link) ? 'active' : ''; ?>" href="<?= $link['href']; ?>">
          <i class="fa-solid <?= $link['icon']; ?>"></i>
          <span><?= $link['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </body>
</html>
