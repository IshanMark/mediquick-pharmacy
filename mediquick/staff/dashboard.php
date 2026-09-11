<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$pendingRx = (int) $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'pending'")->fetchColumn();
$openOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('awaiting_payment','confirmed','packed','dispatched')")->fetchColumn();
$lowStock = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock_qty <= reorder_level AND is_active = 1')->fetchColumn();
$openInquiries = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'open'")->fetchColumn();

$recentOrders = $pdo->query(
    "SELECT o.*, u.full_name FROM orders o JOIN users u ON u.id = o.customer_id
     ORDER BY o.created_at DESC LIMIT 6"
)->fetchAll();

$rxQueue = $pdo->query(
    "SELECT p.*, u.full_name FROM prescriptions p JOIN users u ON u.id = p.customer_id
     WHERE p.status = 'pending' ORDER BY p.uploaded_at ASC LIMIT 5"
)->fetchAll();

$lowStockItems = $pdo->query(
    'SELECT * FROM products WHERE stock_qty <= reorder_level AND is_active = 1 ORDER BY stock_qty ASC LIMIT 5'
)->fetchAll();

$area = 'staff'; $active = 'dashboard'; $pageTitle = 'Operational overview';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $openOrders ?></div><div class="kpi-label">Open orders</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $pendingRx ?></div><div class="kpi-label">Rx to review</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $lowStock ?></div><div class="kpi-label">Low stock items</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $openInquiries ?></div><div class="kpi-label">Open inquiries</div></div></div>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="kpi-tile">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 fw-bold mb-0">Recent orders</h2>
        <a href="<?= url('staff/orders.php') ?>" class="small">View all</a>
      </div>
      <table class="table table-sm-tight mb-0">
        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $o): [$label, $badge] = order_status_badge($o['status']); ?>
          <tr>
            <td><a href="<?= url('staff/order-detail.php?id=' . $o['id']) ?>"><?= e($o['order_no']) ?></a></td>
            <td><?= e($o['full_name']) ?></td>
            <td><?= money((float) $o['total']) ?></td>
            <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-5">
    <div class="kpi-tile mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 fw-bold mb-0">Prescription queue</h2>
        <a href="<?= url('staff/rx-queue.php') ?>" class="small">View all</a>
      </div>
      <?php if (!$rxQueue): ?><p class="small text-muted mb-0">Nothing pending. 🎉</p><?php endif; ?>
      <?php foreach ($rxQueue as $rx): ?>
        <div class="d-flex justify-content-between small border-bottom py-1">
          <span><?= e($rx['full_name']) ?></span>
          <a href="<?= url('staff/rx-review.php?id=' . $rx['id']) ?>">Review</a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="kpi-tile">
      <h2 class="h6 fw-bold mb-2">⚠ Critical stock alerts</h2>
      <?php if (!$lowStockItems): ?><p class="small text-muted mb-0">Stock levels are healthy.</p><?php endif; ?>
      <?php foreach ($lowStockItems as $p): ?>
        <div class="d-flex justify-content-between small border-bottom py-1">
          <span><?= e($p['name']) ?></span>
          <span class="text-danger fw-semibold"><?= $p['stock_qty'] ?> left</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
