<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$statusFilter = $_GET['status'] ?? 'pending';
$where = in_array($statusFilter, ['pending', 'approved', 'rejected'], true) ? 'WHERE p.status = ?' : '';

$stmt = $pdo->prepare(
    "SELECT p.*, u.full_name, u.email FROM prescriptions p JOIN users u ON u.id = p.customer_id
     $where ORDER BY p.uploaded_at DESC"
);
$stmt->execute($where ? [$statusFilter] : []);
$prescriptions = $stmt->fetchAll();

$area = 'staff'; $active = 'rx-queue'; $pageTitle = 'Prescription review queue';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="d-flex gap-2 mb-3">
  <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
    <a href="?status=<?= $key ?>" class="btn btn-sm <?= $statusFilter === $key ? 'btn-dark' : 'btn-outline-secondary' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="kpi-tile">
  <?php if (!$prescriptions): ?>
    <p class="text-muted mb-0">No prescriptions in this list.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Customer</th><th>Doctor</th><th>SLMC no.</th><th>Uploaded</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($prescriptions as $rx): [$label, $badge] = rx_status_badge($rx['status']); ?>
        <tr>
          <td><?= e($rx['full_name']) ?><div class="small text-muted"><?= e($rx['email']) ?></div></td>
          <td><?= e($rx['doctor_name']) ?></td>
          <td><?= e($rx['doctor_reg_no']) ?></td>
          <td><?= date('d M Y', strtotime($rx['uploaded_at'])) ?></td>
          <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
          <td><a href="<?= url('staff/rx-review.php?id=' . $rx['id']) ?>" class="small">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
