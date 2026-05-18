<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/styles/main.css?v=app-expenses-2" />
    <script src="https://kit.fontawesome.com/8fd9367667.js" crossorigin="anonymous"></script>
    <title><?= $title ?? "App"; ?></title>
  </head>
  <body class="app-page <?= $bodyClass ?? ''; ?>">
    <?php include __DIR__.'/../partials/nav.html'; ?>

    <div class="container app-layout">
      <main class="app-main">
        <?= $content ?>
      </main>
    </div>
  </body>
</html>
