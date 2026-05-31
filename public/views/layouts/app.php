<?php
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$username = $_SESSION['username'] ?? 'Guest';
$activeLocale = current_locale();

if (!isset($csrfToken) || !is_string($csrfToken) || $csrfToken === '') {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  $csrfToken = (string) $_SESSION['csrf_token'];
}

$sidebarLinks = [
  ['label' => __('nav.dashboard'), 'href' => '/dashboard', 'icon' => 'fa-chart-line', 'match' => 'dashboard'],
  ['label' => __('nav.expenses'), 'href' => '/expenses', 'icon' => 'fa-wallet', 'match' => 'expenses'],
  ['label' => __('nav.statistics'), 'href' => '/statistics', 'icon' => 'fa-chart-simple', 'match' => 'statistics'],
  ['label' => __('nav.categories'), 'href' => '/categories', 'icon' => 'fa-layer-group', 'match' => 'categories'],
  ['label' => __('nav.profile'), 'href' => '/profile', 'icon' => 'fa-user', 'match' => 'profile'],
];

$mobileLinks = [
  ['label' => __('nav.dashboard'), 'href' => '/dashboard', 'icon' => 'fa-house', 'match' => 'dashboard'],
  ['label' => __('nav.expenses'), 'href' => '/expenses', 'icon' => 'fa-wallet', 'match' => 'expenses', 'exact' => true],
  ['label' => __('common.add'), 'href' => '/expenses/create', 'icon' => 'fa-plus', 'match' => 'expenses/create', 'exact' => true],
  ['label' => __('nav.statistics'), 'href' => '/statistics', 'icon' => 'fa-chart-simple', 'match' => 'statistics'],
  ['label' => __('nav.profile'), 'href' => '/profile', 'icon' => 'fa-user', 'match' => 'profile'],
];

$isActive = static function (array $link) use ($currentPath): bool {
    if (!empty($link['exact'])) {
        return $currentPath === $link['match'];
    }

    return $currentPath === $link['match'] || str_starts_with($currentPath, $link['match'].'/');
};

$currentLabel = __('nav.dashboard');
foreach ($sidebarLinks as $link) {
    if ($isActive($link)) {
        $currentLabel = $link['label'];
        break;
    }
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars((string) $activeLocale, ENT_QUOTES, 'UTF-8'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/styles/main.css?v=lang-dropdown-2" />
    <script src="https://kit.fontawesome.com/8fd9367667.js" crossorigin="anonymous"></script>
    <script src="/scripts/main.js?v=lang-dropdown-2" defer></script>
    <title><?= htmlspecialchars((string) ($title ?? "App"), ENT_QUOTES, 'UTF-8'); ?></title>
  </head>
  <body class="app-page <?= htmlspecialchars((string) ($bodyClass ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="app-shell">
      <aside class="app-sidebar">
        <a class="sidebar-brand" href="/dashboard">
          <span class="app-brand-mark"><i class="fa-solid fa-wallet"></i></span>
          <span>
            <strong><?= htmlspecialchars((string) __('app.name'), ENT_QUOTES, 'UTF-8'); ?></strong>
            <small><?= htmlspecialchars((string) __('app.subtitle'), ENT_QUOTES, 'UTF-8'); ?></small>
          </span>
        </a>

        <nav class="sidebar-nav" aria-label="Primary navigation">
          <?php foreach ($sidebarLinks as $link): ?>
            <a class="sidebar-link <?= $isActive($link) ? 'active' : ''; ?>" href="<?= $link['href']; ?>">
              <i class="fa-solid <?= $link['icon']; ?>"></i>
            <span><?= htmlspecialchars((string) $link['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="sidebar-user">
          <span class="user-avatar"><?= htmlspecialchars((string) strtoupper(substr($username, 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
          <span>
            <strong><?= htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8'); ?></strong>
            <small><?= htmlspecialchars((string) __('profile.account_data'), ENT_QUOTES, 'UTF-8'); ?></small>
          </span>
        </div>
      </aside>

      <div class="app-main">
        <header class="app-topbar">
          <form class="topbar-search" action="/expenses" method="GET">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="search" type="search" placeholder="<?= htmlspecialchars((string) __('expenses.search_placeholder'), ENT_QUOTES, 'UTF-8'); ?>" />
          </form>

          <a class="topbar-link <?= $isActive(['match' => 'dashboard']) ? 'active' : ''; ?>" href="/dashboard"><?= htmlspecialchars((string) strtoupper($currentLabel), ENT_QUOTES, 'UTF-8'); ?></a>

          <form class="locale-switcher" method="POST" action="/language">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
            <button class="locale-flag-btn <?= $activeLocale === 'pl' ? 'active' : ''; ?>" type="submit" name="locale" value="pl" aria-label="<?= htmlspecialchars((string) __('common.polish'), ENT_QUOTES, 'UTF-8'); ?>" title="<?= htmlspecialchars((string) __('common.polish'), ENT_QUOTES, 'UTF-8'); ?>">
              <span class="flag-icon flag-pl" aria-hidden="true"></span>
            </button>
            <button class="locale-flag-btn <?= $activeLocale === 'en' ? 'active' : ''; ?>" type="submit" name="locale" value="en" aria-label="<?= htmlspecialchars((string) __('common.english'), ENT_QUOTES, 'UTF-8'); ?>" title="<?= htmlspecialchars((string) __('common.english'), ENT_QUOTES, 'UTF-8'); ?>">
              <span class="flag-icon flag-gb" aria-hidden="true"></span>
            </button>
          </form>
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
          <span><?= htmlspecialchars((string) $link['label'], ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </body>
</html>
