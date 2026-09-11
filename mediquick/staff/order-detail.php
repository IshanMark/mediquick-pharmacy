<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$staff = current_user();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON u.id = o.customer_id WHERE o.id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

// What each current status is allowed to move to next. Keeps the workflow linear and auditable.
$transitions = [
    'confirmed'  => ['packed', 'cancelled'],
    'packed'     => ['dispatched'],
    'dispatched' => ['delivered'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    csrf_check();
    $newStatus = $_POST['new_status'];
    $allowed = $transitions[$order['status']] ?? [];

    if (!in_array($newStatus, $allowed, true)) {
        flash('error', 'That status change is not allowed from the current status.');
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE orders SET status = ?, handled_by = ? WHERE id = ?')
                ->execute([$newStatus, $staff['id'], $order['id']]);

            // Stock was already deducted when the order was confirmed (see checkout.php / payhere/notify.php);
            // cancelling from here only applies before packing, so restore it.
            if ($newStatus === 'cancelled') {
                $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
                $items->execute([$order['id']]);
                $restock = $pdo->prepare('UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?');
                foreach ($items->fetchAll() as $line) {
                    $restock->execute([$line['quantity'], $line['product_id']]);
                }
            }

            $pdo->commit();
            log_action($pdo, $staff['id'], 'order.status.' . $newStatus, 'order', $order['id']);
            notify($pdo, $order['customer_id'], 'Order update', "Order {$order['order_no']} is now " . order_status_badge($newStatus)[0] . '.', 'account/order-detail.php?id=' . $order['id']);
            flash('success', 'Order updated.');
        } catch (Exception $ex) {
            $pdo->rollBack();
            error_log('Order status update failed: ' . $ex->getMessage());
            flash('error', 'Could not update the order. Please try again.');
        }
        redirect('staff/order-detail.php?id=' . $order['id']);
    }
}

$itemsStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

[$label, $badge] = order_status_badge($order['status']);
$nextOptions = $transitions[$order['status']] ?? [];

$area = 'staff'; $active = 'orders'; $pageTitle = 'Order ' . $order['order_no'];
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-4">
  <div class="col-md-8">
    <div class="kpi-tile mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge <?= $badge ?> fs-6"><?= $label ?></span>
        <span class="small text-muted">Placed <?= date('d M Y, g:i A', strtotime($order['created_at'])) ?></span>
      </div>
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
      <div class="d-flex justify-content-between fw-bold h5"><span>Total</span><span><?= money((float) $order['total']) ?></span></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-tile mb-3">
      <h2 class="h6 fw-bold">Customer</h2>
      <p class="small mb-1"><?= e($order['full_name']) ?> (<?= e($order['email']) ?>)</p>
      <p class="small mb-1"><?= e($order['delivery_address']) ?>, <?= e($order['delivery_city']) ?></p>
      <p class="small mb-0">Phone: <?= e($order['contact_phone']) ?></p>
    </div>
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Update status</h2>
      <?php if (!$nextOptions): ?>
        <p class="small text-muted mb-0">No further action needed here.</p>
      <?php else: ?>
        <form method="post">
          <?= csrf_field() ?>
          <?php foreach ($nextOptions as $opt): [$optLabel] = order_status_badge($opt); ?>
            <button type="submit" name="new_status" value="<?= $opt ?>" class="btn btn-sm <?= $opt === 'cancelled' ? 'btn-outline-danger' : 'btn-outline-success' ?> mb-1 w-100">
              Mark as <?= $optLabel ?>
            </button>
          <?php endforeach; ?>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
