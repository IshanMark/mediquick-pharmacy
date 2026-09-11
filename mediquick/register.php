<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($fullName === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Enter a valid phone number.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (role, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute(['customer', $fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
        $userId = (int) $pdo->lastInsertId();

        login_user(['id' => $userId, 'role' => 'customer', 'full_name' => $fullName, 'email' => $email]);
        flash('success', 'Welcome to MediQuick, ' . $fullName . '!');
        redirect('account/dashboard.php');
    }
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5" style="max-width:480px">
  <h1 class="h3 fw-bold text-center mb-1">Welcome to MediQuick</h1>
  <p class="text-muted text-center mb-4">Create an account to order and track your medicines.</p>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger small"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label small" for="full_name">Full name</label>
      <input id="full_name" name="full_name" class="form-control" required value="<?= e($_POST['full_name'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small" for="email">Email</label>
      <input id="email" name="email" type="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small" for="phone">Phone</label>
      <input id="phone" name="phone" class="form-control" required value="<?= e($_POST['phone'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small" for="password">Password (min 8 characters)</label>
      <input id="password" name="password" type="password" class="form-control" minlength="8" required>
    </div>
    <div class="mb-3">
      <label class="form-label small" for="confirm_password">Confirm password</label>
      <input id="confirm_password" name="confirm_password" type="password" class="form-control" minlength="8" required>
    </div>
    <button type="submit" class="btn w-100" style="background:var(--brand);color:#fff">Create account</button>
    <p class="text-center small text-muted mt-3">Already have an account? <a href="<?= url('login.php') ?>">Log in</a></p>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
