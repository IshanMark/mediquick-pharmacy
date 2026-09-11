<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();
$errors = [];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['update_profile'])) {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');

        if ($fullName === '') $errors[] = 'Full name is required.';
        if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Enter a valid phone number.';

        if (!$errors) {
            $pdo->prepare('UPDATE users SET full_name = ?, phone = ?, address = ?, city = ? WHERE id = ?')
                ->execute([$fullName, $phone, $address, $city, $user['id']]);
            $_SESSION['user']['name'] = $fullName;
            flash('success', 'Profile updated.');
            redirect('account/profile.php');
        }
    }

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $profile['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Password changed.');
            redirect('account/profile.php');
        }
    }
}

$active = 'profile';
$pageTitle = 'Profile';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-4">Profile</h1>
  <div class="row">
    <div class="col-md-3"><?php require __DIR__ . '/_nav.php'; ?></div>
    <div class="col-md-9">
      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger small"><?= e($err) ?></div>
      <?php endforeach; ?>

      <div class="kpi-tile mb-4">
        <h2 class="h6 fw-bold">Your details</h2>
        <form method="post">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small">Full name</label>
              <input name="full_name" class="form-control" required value="<?= e($profile['full_name']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small">Email (fixed)</label>
              <input class="form-control" value="<?= e($profile['email']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label small">Phone</label>
              <input name="phone" class="form-control" required value="<?= e($profile['phone']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label small">City</label>
              <input name="city" class="form-control" value="<?= e($profile['city'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label small">Delivery address</label>
              <input name="address" class="form-control" value="<?= e($profile['address'] ?? '') ?>">
            </div>
          </div>
          <button type="submit" name="update_profile" value="1" class="btn mt-3" style="background:var(--brand);color:#fff">Save changes</button>
        </form>
      </div>

      <div class="kpi-tile">
        <h2 class="h6 fw-bold">Change password</h2>
        <form method="post">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small">Current password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">New password</label>
              <input type="password" name="new_password" class="form-control" minlength="8" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Confirm new password</label>
              <input type="password" name="confirm_password" class="form-control" minlength="8" required>
            </div>
          </div>
          <button type="submit" name="change_password" value="1" class="btn btn-outline-secondary mt-3">Change password</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
