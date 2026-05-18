<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="/styles/main.css" />
    <script src="https://kit.fontawesome.com/8fd9367667.js" crossorigin="anonymous"></script>
    <title><?= $title ?? "App"; ?></title>
  </head>
  <body>
    <?php include __DIR__.'/../partials/nav.html'; ?>

    <div class="container layout">
      <main>
        <?= $content ?>
      </main>
      <aside>
        <p>TODO later</p>
      </aside>
      <footer>2026</footer>
    </div>
  </body>
</html>
