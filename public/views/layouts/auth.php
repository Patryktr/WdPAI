<?php
$activeLocale = current_locale();

if (!isset($csrfToken) || !is_string($csrfToken) || $csrfToken === '') {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  $csrfToken = (string) $_SESSION['csrf_token'];
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars((string) $activeLocale, ENT_QUOTES, 'UTF-8'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/styles/main.css?v=lang-dropdown-2">
    <script src="https://kit.fontawesome.com/8fd9367667.js" crossorigin="anonymous"></script>
    <title><?= htmlspecialchars((string) ($title ?? "Auth"), ENT_QUOTES, 'UTF-8'); ?></title>
  </head>
  <body class="auth-page">
    <form class="auth-locale-switcher" method="POST" action="/language">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
      <button class="locale-flag-btn <?= $activeLocale === 'pl' ? 'active' : ''; ?>" type="submit" name="locale" value="pl" aria-label="<?= htmlspecialchars((string) __('common.polish'), ENT_QUOTES, 'UTF-8'); ?>" title="<?= htmlspecialchars((string) __('common.polish'), ENT_QUOTES, 'UTF-8'); ?>">
        <span class="flag-icon flag-pl" aria-hidden="true"></span>
      </button>
      <button class="locale-flag-btn <?= $activeLocale === 'en' ? 'active' : ''; ?>" type="submit" name="locale" value="en" aria-label="<?= htmlspecialchars((string) __('common.english'), ENT_QUOTES, 'UTF-8'); ?>" title="<?= htmlspecialchars((string) __('common.english'), ENT_QUOTES, 'UTF-8'); ?>">
        <span class="flag-icon flag-gb" aria-hidden="true"></span>
      </button>
    </form>
    <?= $content ?>
  </body>
</html>
