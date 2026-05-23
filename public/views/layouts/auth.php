<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/styles/main.css?v=auth-dark-5">
    <script src="https://kit.fontawesome.com/8fd9367667.js" crossorigin="anonymous"></script>
    <title><?= htmlspecialchars((string) ($title ?? "Auth"), ENT_QUOTES, 'UTF-8'); ?></title>
  </head>
  <body class="auth-page">
    <?= $content ?>
  </body>
</html>
