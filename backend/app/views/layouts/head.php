<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Mbagathi Beauty Parlour') ?> | Mbagathi</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body>
<div id="sidebar-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:99;"
     onclick="this.style.display='none';document.querySelector('.sidebar').classList.remove('open')"></div>
<div class="app-shell">
