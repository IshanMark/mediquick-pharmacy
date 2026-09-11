<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();

$pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$user['id']]);

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30');
$stmt->execute([$user['id']]);
$notifications = $stmt->fetchAll();

$active = 'notifications';
$pageTitle = 'Notifications';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-4">Notifications</h1>
  <div class="row">
    <div class="col-md-3"><?php require __DIR__ . '/_nav.php'; ?></div>
    <div class="col-md-9">
      <?php if (!$notifications): ?>
        <div class="alert alert-light border">Nothing here yet.</div>
      <?php else: ?>
        <?php foreach ($notifications as $n): ?>
          <div class="kpi-tile mb-2">
            <div class="fw-semibold small"><?= e($n['title']) ?></div>
            <div class="small text-muted"><?= e($n['message']) ?></div>
            <div class="small text-muted"><?= date('d M Y, g:i A', strtotime($n['created_at'])) ?></div>
            <?php if ($n['link']): ?><a class="small" href="<?= url($n['link']) ?>">View</a><?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
