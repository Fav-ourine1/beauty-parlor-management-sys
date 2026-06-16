<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In | Mbagathi Beauty Parlour</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <span class="logo-icon">💅</span>
      <h1>Mbagathi Beauty Parlour</h1>
      <p>Sign in to your account</p>
    </div>

    <?php if (!empty($flash)): ?>
      <div class="alert alert-error"><?= e($flash) ?></div>
    <?php endif; ?>

    <div id="login-error" class="alert alert-error" style="display:none"></div>

    <form id="login-form" novalidate>
      <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <input class="form-control" type="email" id="email" name="email"
               placeholder="your@email.com" autocomplete="email" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password"
               placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button class="btn btn-primary btn-block btn-lg" type="submit" style="margin-top:8px">
        Sign In
      </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-muted)">
      New client?
      <a href="<?= APP_URL ?>/register">Create an account</a>
    </p>
  </div>
</div>
<script src="<?= APP_URL ?>/js/app.js"></script>
</body>
</html>
