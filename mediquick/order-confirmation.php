<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('customer');

$user = current_user();
$id = (int) ($_GET['order'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND customer_id = ?');
$stmt->execute([$id, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

[$statusLabel, $badgeClass] = order_status_badge($order['status']);
$isPendingRx = $order['status'] === 'pending_verification';

$pageTitle = 'Order confirmed';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
  <div class="fs-1 text-success mb-2"><?= $isPendingRx ? '🕒' : '✔' ?></div>
  <h1 class="h3 fw-bold"><?= $isPendingRx ? 'Order placed — awaiting pharmacist review' : 'Thank you for your order!' ?></h1>
  <p class="text-muted">Order <strong><?= e($order['order_no']) ?></strong> — <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></p>
  <?php if ($isPendingRx): ?>
    <p class="text-muted">Total: <?= money((float) $order['total']) ?>. A pharmacist will check your prescription before this order proceeds — you won't be charged until then. We'll notify you either way.</p>
  <?php else: ?>
    <p class="text-muted">Total paid: <?= money((float) $order['total']) ?> · <?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Card payment' ?></p>
  <?php endif; ?>
  <div class="mt-4">
    <a href="<?= url('account/order-detail.php?id=' . $order['id']) ?>" class="btn" style="background:var(--brand);color:#fff">Track this order</a>
    <a href="<?= url('shop.php') ?>" class="btn btn-outline-secondary">Continue shopping</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
