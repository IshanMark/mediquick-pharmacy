<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$statusFilter = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($statusFilter !== '') {
    $where = 'WHERE o.status = ?';
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare(
    "SELECT o.*, u.full_name FROM orders o JOIN users u ON u.id = o.customer_id
     $where ORDER BY o.created_at DESC LIMIT 100"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statuses = ['', 'pending_verification', 'awaiting_payment', 'confirmed', 'packed', 'dispatched', 'delivered', 'cancelled'];

$area = 'staff'; $active = 'orders'; $pageTitle = 'Orders';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="d-flex gap-2 mb-3 flex-wrap">
  <?php foreach ($statuses as $s): ?>
    <a href="?status=<?= e($s) ?>" class="btn btn-sm <?= $statusFilter === $s ? 'btn-dark' : 'btn-outline-secondary' ?>">
      <?= $s === '' ? 'All' : order_status_badge($s)[0] ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="kpi-tile">
  <?php if (!$orders): ?>
    <p class="text-muted mb-0">No orders in this view.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): [$label, $badge] = order_status_badge($o['status']); ?>
        <tr>
          <td><?= e($o['order_no']) ?></td>
          <td><?= e($o['full_name']) ?></td>
          <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td><?= money((float) $o['total']) ?></td>
          <td><?= $o['payment_method'] === 'cod' ? 'COD' : 'Card' ?> · <?= e($o['payment_status']) ?></td>
          <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
          <td><a href="<?= url('staff/order-detail.php?id=' . $o['id']) ?>" class="small">Manage</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
