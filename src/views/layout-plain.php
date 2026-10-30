<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#fcfcfa">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title ?? 'Notvorrat') ?> · Notvorrat</title>
<link rel="icon" href="<?= e(asset('mark.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body class="page-plain">
<main class="gate">
  <?= $content ?>
</main>
</body>
</html>
