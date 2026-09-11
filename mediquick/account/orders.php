<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$active = 'orders';
$pageTitle = 'My orders';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-4">My orders</h1>
  <div class="row">
    <div class="col-md-3"><?php require __DIR__ . '/_nav.php'; ?></div>
    <div class="col-md-9">
      <?php if (!$orders): ?>
        <div class="alert alert-light border">You haven't placed any orders yet.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o):
                [$label, $badge] = order_status_badge($o['status']);
                $count = $pdo->prepare('SELECT SUM(quantity) FROM order_items WHERE order_id = ?');
                $count->execute([$o['id']]);
            ?>
              <tr>
                <td><?= e($o['order_no']) ?></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><?= (int) $count->fetchColumn() ?></td>
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
