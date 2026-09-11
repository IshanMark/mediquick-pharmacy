<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$byStatus = $pdo->query(
    'SELECT status, COUNT(*) c, COALESCE(SUM(total),0) rev FROM orders GROUP BY status'
)->fetchAll();

$topProducts = $pdo->query(
    "SELECT p.name, SUM(oi.quantity) qty, SUM(oi.quantity * oi.unit_price) rev
     FROM order_items oi JOIN products p ON p.id = oi.product_id
     JOIN orders o ON o.id = oi.order_id
     WHERE o.status NOT IN ('cancelled')
     GROUP BY p.id ORDER BY qty DESC LIMIT 8"
)->fetchAll();

$monthly = $pdo->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COALESCE(SUM(total),0) rev
     FROM orders WHERE status NOT IN ('cancelled','pending_verification')
     GROUP BY ym ORDER BY ym DESC LIMIT 6"
)->fetchAll();

$area = 'admin'; $active = 'reports'; $pageTitle = 'Reports';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-4">
  <div class="col-md-6">
    <div class="kpi-tile mb-4">
      <h2 class="h6 fw-bold">Orders by status</h2>
      <table class="table table-sm-tight mb-0">
        <thead><tr><th>Status</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($byStatus as $row): [$label, $badge] = order_status_badge($row['status']); ?>
          <tr>
            <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
            <td><?= $row['c'] ?></td>
            <td><?= money((float) $row['rev']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Revenue by month</h2>
      <table class="table table-sm-tight mb-0">
        <thead><tr><th>Month</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($monthly as $row): ?>
          <tr><td><?= date('M Y', strtotime($row['ym'] . '-01')) ?></td><td><?= money((float) $row['rev']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Top-selling products</h2>
      <table class="table table-sm-tight mb-0">
        <thead><tr><th>Product</th><th>Units sold</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($topProducts as $row): ?>
          <tr><td><?= e($row['name']) ?></td><td><?= (int) $row['qty'] ?></td><td><?= money((float) $row['rev']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
