<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Mbagathi Beauty Parlour') ?> | Mbagathi</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body class="public-page">

<header class="public-header">
  <div class="public-header-inner">
    <a href="<?= APP_URL ?>/" class="public-logo">
      <span class="logo-icon">💅</span>
      <span class="logo-name">Mbagathi</span>
      <span class="logo-sub">Beauty Parlour</span>
    </a>
    <nav class="public-nav">
      <a href="<?= APP_URL ?>/about"   class="<?= str_contains($_SERVER['REQUEST_URI'], '/about')   ? 'active' : '' ?>">About</a>
      <a href="<?= APP_URL ?>/contact" class="<?= str_contains($_SERVER['REQUEST_URI'], '/contact') ? 'active' : '' ?>">Contact</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="<?= APP_URL ?>/<?= e($_SESSION['user']['role']) ?>/dashboard" class="btn btn-secondary btn-sm">My Account</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login"    class="btn btn-secondary btn-sm">Login</a>
        <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-sm">Book Now</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="public-main">
