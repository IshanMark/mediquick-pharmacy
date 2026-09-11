<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$admin = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['create_staff'])) {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'staff';
        $password = $_POST['password'] ?? '';

        if ($fullName === '') $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
        if (!in_array($role, ['staff', 'admin'], true)) $errors[] = 'Invalid role.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) $errors[] = 'That email is already registered.';
        }

        if (!$errors) {
            $pdo->prepare('INSERT INTO users (role, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)')
                ->execute([$role, $fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
            $newId = (int) $pdo->lastInsertId();
            log_action($pdo, $admin['id'], 'user.create_' . $role, 'user', $newId);
            flash('success', ucfirst($role) . ' account created.');
            redirect('admin/staff.php');
        }
    }

    if (isset($_POST['toggle_status'])) {
        $id = (int) $_POST['toggle_status'];
        if ($id === $admin['id']) {
            flash('error', "You can't suspend your own account.");
        } else {
            $pdo->prepare("UPDATE users SET status = IF(status='active','suspended','active') WHERE id = ? AND role IN ('staff','admin')")
                ->execute([$id]);
            log_action($pdo, $admin['id'], 'user.toggle_status', 'user', $id);
            flash('success', 'Account status updated.');
        }
        redirect('admin/staff.php');
    }
}

$staffList = $pdo->query("SELECT * FROM users WHERE role IN ('staff','admin') ORDER BY created_at DESC")->fetchAll();

$area = 'admin'; $active = 'staff'; $pageTitle = 'Staff accounts';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-4">
  <div class="col-md-5">
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Create a staff or admin account</h2>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger small"><?= e($err) ?></div><?php endforeach; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="mb-2">
          <label class="form-label small">Full name</label>
          <input name="full_name" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Email</label>
          <input name="email" type="email" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Phone</label>
          <input name="phone" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Role</label>
          <select name="role" class="form-select">
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small">Temporary password (min 8 chars)</label>
          <input name="password" type="password" class="form-control" minlength="8" required>
        </div>
        <button type="submit" name="create_staff" value="1" class="btn w-100" style="background:var(--brand);color:#fff">Create account</button>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">All staff &amp; admin accounts</h2>
      <table class="table">
        <thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($staffList as $s): ?>
          <tr>
            <td><?= e($s['full_name']) ?></td>
            <td><?= ucfirst($s['role']) ?></td>
            <td><?= e($s['email']) ?></td>
            <td><span class="badge <?= $s['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= ucfirst($s['status']) ?></span></td>
            <td>
              <?php if ((int) $s['id'] !== $admin['id']): ?>
                <form method="post" onsubmit="return confirm('<?= $s['status'] === 'active' ? 'Suspend' : 'Reactivate' ?> this account?')">
                  <?= csrf_field() ?>
                  <button type="submit" name="toggle_status" value="<?= $s['id'] ?>" class="btn btn-link btn-sm p-0 small">
                    <?= $s['status'] === 'active' ? 'Suspend' : 'Reactivate' ?>
                  </button>
                </form>
              <?php else: ?>
                <span class="small text-muted">You</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
