<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND customer_id = ?');
$stmt->execute([$id, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    csrf_check();
    if (in_array($order['status'], ['pending_verification', 'awaiting_payment', 'confirmed'], true)) {
        $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$order['id']]);
        log_action($pdo, $user['id'], 'order.cancel', 'order', $order['id']);
        flash('success', 'Order cancelled.');
        redirect('account/order-detail.php?id=' . $order['id']);
    }
}

$itemsStmt = $pdo->prepare(
    'SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?'
);
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

[$label, $badge] = order_status_badge($order['status']);

$steps = ['confirmed', 'packed', 'dispatched', 'delivered'];
$currentStep = array_search($order['status'], $steps, true);

$active = 'orders';
$pageTitle = 'Order ' . $order['order_no'];
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-1">Order <?= e($order['order_no']) ?></h1>
  <p class="text-muted mb-4">Placed <?= date('d M Y, g:i A', strtotime($order['created_at'])) ?></p>

  <div class="row">
    <div class="col-md-3"><?php require __DIR__ . '/_nav.php'; ?></div>
    <div class="col-md-9">
      <div class="kpi-tile mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="badge <?= $badge ?> fs-6"><?= $label ?></span>
          <?php if (in_array($order['status'], ['pending_verification', 'awaiting_payment', 'confirmed'], true)): ?>
            <form method="post" onsubmit="return confirm('Cancel this order?')">
              <?= csrf_field() ?>
              <button type="submit" name="cancel_order" value="1" class="btn btn-sm btn-outline-danger">Cancel order</button>
            </form>
          <?php endif; ?>
        </div>

        <?php if ($order['status'] !== 'cancelled'): ?>
          <div class="d-flex justify-content-between small text-muted mb-4">
            <?php foreach ($steps as $i => $step): ?>
              <span class="<?= $currentStep !== false && $i <= $currentStep ? 'text-success fw-bold' : '' ?>">
                <?= ucfirst($step) ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <table class="table mb-0">
          <thead><tr><th>Item</th><th>Qty</th><th>Unit price</th><th>Total</th></tr></thead>
          <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= e($item['name']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td><?= money((float) $item['unit_price']) ?></td>
              <td><?= money($item['quantity'] * (float) $item['unit_price']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <hr>
        <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span><?= money((float) $order['subtotal']) ?></span></div>
        <div class="d-flex justify-content-between small mb-2"><span>Delivery</span><span><?= money((float) $order['delivery_fee']) ?></span></div>
        <div class="d-flex justify-content-between fw-bold h5"><span>Total</span><span><?= money((float) $order['total']) ?></span></div>
      </div>

      <div class="kpi-tile">
        <h2 class="h6 fw-bold">Delivery details</h2>
        <p class="small text-muted mb-1"><?= e($order['delivery_address']) ?>, <?= e($order['delivery_city']) ?></p>
        <p class="small text-muted mb-0">Phone: <?= e($order['contact_phone']) ?> · Payment: <?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Card' ?> (<?= e($order['payment_status']) ?>)</p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
