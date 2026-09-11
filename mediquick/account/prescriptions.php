<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();
$stmt = $pdo->prepare('SELECT * FROM prescriptions WHERE customer_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$user['id']]);
$prescriptions = $stmt->fetchAll();

$active = 'prescriptions';
$pageTitle = 'My prescriptions';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0">My prescriptions</h1>
    <a href="<?= url('prescription-upload.php') ?>" class="btn btn-sm" style="background:var(--brand);color:#fff">Upload new</a>
  </div>
  <div class="row">
    <div class="col-md-3"><?php require __DIR__ . '/_nav.php'; ?></div>
    <div class="col-md-9">
      <?php if (!$prescriptions): ?>
        <div class="alert alert-light border">You haven't uploaded a prescription yet.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Doctor</th><th>SLMC no.</th><th>Uploaded</th><th>Status</th><th>Pharmacist note</th></tr></thead>
            <tbody>
            <?php foreach ($prescriptions as $rx): [$label, $badge] = rx_status_badge($rx['status']); ?>
              <tr>
                <td><?= e($rx['doctor_name']) ?></td>
                <td><?= e($rx['doctor_reg_no']) ?></td>
                <td><?= date('d M Y', strtotime($rx['uploaded_at'])) ?></td>
                <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                <td class="small text-muted"><?= e($rx['review_note'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
