<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account | Mbagathi Beauty Parlour</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card" style="max-width:480px">
    <div class="auth-logo">
      <span class="logo-icon">💅</span>
      <h1>Create an Account</h1>
      <p>Join Mbagathi Beauty Parlour</p>
    </div>

    <div id="register-error" class="alert alert-error" style="display:none"></div>

    <form id="register-form" novalidate>
      <div class="form-group">
        <label class="form-label" for="full_name">Full name</label>
        <input class="form-control" type="text" id="full_name" name="full_name"
               placeholder="Jane Njeri" autocomplete="name" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input class="form-control" type="email" id="email" name="email"
                 placeholder="jane@email.com" autocomplete="email" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="phone">Phone</label>
          <input class="form-control" type="tel" id="phone" name="phone"
                 placeholder="07XXXXXXXX" autocomplete="tel" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input class="form-control" type="password" id="password" name="password"
                 placeholder="••••••••" autocomplete="new-password" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="password_confirm">Confirm password</label>
          <input class="form-control" type="password" id="password_confirm" name="password_confirm"
                 placeholder="••••••••" autocomplete="new-password" required>
        </div>
      </div>
      <button class="btn btn-primary btn-block btn-lg" type="submit" style="margin-top:8px">
        Create Account
      </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:.85rem;color:var(--text-muted)">
      Already have an account?
      <a href="<?= APP_URL ?>/login">Sign in</a>
    </p>
  </div>
</div>
<script src="<?= APP_URL ?>/js/app.js"></script>
</body>
</html>
