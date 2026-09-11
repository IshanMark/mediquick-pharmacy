<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Incorrect email or password.';
    } elseif ($user['status'] === 'suspended') {
        $errors[] = 'This account has been suspended. Contact support.';
    }

    if (!$errors) {
        login_user($user);
        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
        log_action($pdo, $user['id'], 'auth.login', 'user', (int) $user['id']);

        $redirectTo = $_SESSION['redirect_after_login'] ?? null;
        unset($_SESSION['redirect_after_login']);

        if ($redirectTo) {
            header('Location: ' . $redirectTo);
            exit;
        }

        redirect(match ($user['role']) {
            'staff' => 'staff/dashboard.php',
            'admin' => 'admin/dashboard.php',
            default => 'account/dashboard.php',
        });
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5" style="max-width:420px">
  <h1 class="h3 fw-bold text-center mb-1">Log in</h1>
  <p class="text-muted text-center mb-4">Customers, staff and admins all log in here.</p>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger small"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label small" for="email">Email</label>
      <input id="email" name="email" type="email" class="form-control" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small" for="password">Password</label>
      <input id="password" name="password" type="password" class="form-control" required>
    </div>
    <button type="submit" class="btn w-100" style="background:var(--brand);color:#fff">Log in</button>
    <p class="text-center small text-muted mt-3">New here? <a href="<?= url('register.php') ?>">Create an account</a></p>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
