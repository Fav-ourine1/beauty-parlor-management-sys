<?php
$currentUser = $_SESSION['user'] ?? [];
$role        = $currentUser['role'] ?? 'client';
$initials    = strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1) . (strpos($currentUser['full_name'] ?? '', ' ') !== false ? substr(strrchr($currentUser['full_name'], ' '), 1, 1) : ''));
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <span class="logo-icon">💅</span>
    <span class="logo-name">Mbagathi</span>
    <span class="logo-sub">Beauty Parlour</span>
  </div>

  <nav class="sidebar-nav">

    <?php if ($role === 'admin'): ?>
      <div class="nav-section"><span class="nav-section-label">Overview</span></div>
      <a href="<?= APP_URL ?>/admin/dashboard" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/dashboard') ? 'active' : '' ?>">
        <span class="nav-icon">📊</span> Dashboard
      </a>

      <div class="nav-section"><span class="nav-section-label">Operations</span></div>
      <a href="<?= APP_URL ?>/admin/appointments" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/appointments') ? 'active' : '' ?>">
        <span class="nav-icon">📅</span> Appointments
      </a>
      <a href="<?= APP_URL ?>/admin/staff" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/staff') ? 'active' : '' ?>">
        <span class="nav-icon">👩‍💼</span> Staff
      </a>
      <a href="<?= APP_URL ?>/admin/services" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/services') ? 'active' : '' ?>">
        <span class="nav-icon">✂️</span> Services
      </a>
      <a href="<?= APP_URL ?>/admin/inventory" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/inventory') ? 'active' : '' ?>">
        <span class="nav-icon">📦</span> Inventory
      </a>

      <div class="nav-section"><span class="nav-section-label">Finance</span></div>
      <a href="<?= APP_URL ?>/admin/reports" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/reports') ? 'active' : '' ?>">
        <span class="nav-icon">💰</span> Reports
      </a>

    <?php elseif ($role === 'staff'): ?>
      <div class="nav-section"><span class="nav-section-label">My Work</span></div>
      <a href="<?= APP_URL ?>/staff/dashboard" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/staff/dashboard') ? 'active' : '' ?>">
        <span class="nav-icon">🏠</span> Dashboard
      </a>
      <a href="<?= APP_URL ?>/staff/schedule" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/staff/schedule') ? 'active' : '' ?>">
        <span class="nav-icon">📅</span> My Schedule
      </a>
      <a href="<?= APP_URL ?>/admin/inventory" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/inventory') ? 'active' : '' ?>">
        <span class="nav-icon">📦</span> Inventory
      </a>

    <?php else: ?>
      <div class="nav-section"><span class="nav-section-label">My Account</span></div>
      <a href="<?= APP_URL ?>/client/dashboard" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/client/dashboard') ? 'active' : '' ?>">
        <span class="nav-icon">🏠</span> My Bookings
      </a>
      <a href="<?= APP_URL ?>/client/book" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/client/book') ? 'active' : '' ?>">
        <span class="nav-icon">✨</span> Book Appointment
      </a>
    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= e($initials) ?></div>
      <div class="user-info">
        <div class="user-name"><?= e($currentUser['full_name'] ?? '') ?></div>
        <div class="user-role"><?= e($role) ?></div>
      </div>
    </div>
    <button class="btn-logout" id="btn-logout">Sign Out</button>
  </div>
</aside>

<div class="main-content">
  <header class="page-header">
    <button class="hamburger" aria-label="Menu">☰</button>
    <div>
      <div class="page-title"><?= e($pageTitle ?? '') ?></div>
      <?php if (!empty($pageSubtitle)): ?>
        <div class="page-subtitle"><?= e($pageSubtitle) ?></div>
      <?php endif; ?>
    </div>
    <div><?php if (!empty($pageAction)) echo $pageAction; ?></div>
  </header>
  <div class="page-body">
