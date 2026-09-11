<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();

$ordersCount = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = ?');
$ordersCount->execute([$user['id']]);
$ordersCount = (int) $ordersCount->fetchColumn();

$pendingRx = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE customer_id = ? AND status = 'pending'");
$pendingRx->execute([$user['id']]);
$pendingRx = (int) $pendingRx->fetchColumn();

$recentOrders = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5');
$recentOrders->execute([$user['id']]);
$recentOrders = $recentOrders->fetchAll();

$active = 'dashboard';
$pageTitle = 'My account';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-4">Welcome back, <?= e($user['name']) ?></h1>
  <div class="row">
    <div class="col-md-3"><?php require __DIR__ . '/_nav.php'; ?></div>
    <div class="col-md-9">
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-4"><div class="kpi-tile"><div class="kpi-value"><?= $ordersCount ?></div><div class="kpi-label">Total orders</div></div></div>
        <div class="col-6 col-md-4"><div class="kpi-tile"><div class="kpi-value"><?= $pendingRx ?></div><div class="kpi-label">Rx pending review</div></div></div>
        <div class="col-6 col-md-4"><div class="kpi-tile"><div class="kpi-value"><?= cart_count() ?></div><div class="kpi-label">Items in cart</div></div></div>
      </div>

      <h2 class="h6 fw-bold">Recent orders</h2>
      <?php if (!$recentOrders): ?>
        <div class="alert alert-light border">No orders yet. <a href="<?= url('shop.php') ?>">Start shopping</a>.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm-tight">
            <thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $o): [$label, $badge] = order_status_badge($o['status']); ?>
              <tr>
                <td><?= e($o['order_no']) ?></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><?= money((float) $o['total']) ?></td>
                <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                <td><a href="<?= url('account/order-detail.php?id=' . $o['id']) ?>" class="small">View</a></td>
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
