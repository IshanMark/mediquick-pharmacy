<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$staff = current_user();
$replyId = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int) $_POST['inquiry_id'];
    $reply = trim($_POST['reply'] ?? '');

    if ($reply !== '') {
        $stmt = $pdo->prepare('SELECT * FROM inquiries WHERE id = ?');
        $stmt->execute([$id]);
        $inq = $stmt->fetch();

        $pdo->prepare(
            "UPDATE inquiries SET reply = ?, replied_by = ?, replied_at = NOW(), status = 'answered' WHERE id = ?"
        )->execute([$reply, $staff['id'], $id]);

        if ($inq && $inq['customer_id']) {
            notify($pdo, (int) $inq['customer_id'], 'Your inquiry was answered', $reply, 'account/dashboard.php');
        }
        log_action($pdo, $staff['id'], 'inquiry.reply', 'inquiry', $id);
        flash('success', 'Reply sent.');
    }
    redirect('staff/inquiries.php');
}

$statusFilter = $_GET['status'] ?? 'open';
$where = in_array($statusFilter, ['open', 'answered', 'closed'], true) ? 'WHERE status = ?' : '';
$stmt = $pdo->prepare("SELECT * FROM inquiries $where ORDER BY created_at DESC");
$stmt->execute($where ? [$statusFilter] : []);
$inquiries = $stmt->fetchAll();

$area = 'staff'; $active = 'inquiries'; $pageTitle = 'Customer inquiries';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="d-flex gap-2 mb-3">
  <?php foreach (['open' => 'Open', 'answered' => 'Answered', 'closed' => 'Closed', '' => 'All'] as $key => $label): ?>
    <a href="?status=<?= $key ?>" class="btn btn-sm <?= $statusFilter === $key ? 'btn-dark' : 'btn-outline-secondary' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$inquiries): ?>
  <div class="kpi-tile"><p class="text-muted mb-0">No inquiries here.</p></div>
<?php endif; ?>

<?php foreach ($inquiries as $inq): ?>
  <div class="kpi-tile mb-3">
    <div class="d-flex justify-content-between">
      <div>
        <div class="fw-semibold"><?= e($inq['subject']) ?></div>
        <div class="small text-muted"><?= e($inq['name']) ?> · <?= e($inq['email']) ?> · <?= date('d M Y, g:i A', strtotime($inq['created_at'])) ?></div>
      </div>
      <span class="badge <?= $inq['status'] === 'open' ? 'text-bg-warning' : ($inq['status'] === 'answered' ? 'text-bg-success' : 'text-bg-secondary') ?>"><?= ucfirst($inq['status']) ?></span>
    </div>
    <p class="mt-2 mb-2"><?= nl2br(e($inq['message'])) ?></p>
    <?php if ($inq['reply']): ?>
      <div class="alert alert-light border small"><strong>Reply:</strong> <?= nl2br(e($inq['reply'])) ?></div>
    <?php else: ?>
      <form method="post" class="d-flex gap-2">
        <?= csrf_field() ?>
        <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
        <input type="text" name="reply" class="form-control" placeholder="Write a reply…" required>
        <button type="submit" class="btn btn-sm" style="background:var(--brand);color:#fff">Send</button>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
