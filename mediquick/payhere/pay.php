<?php
/** Builds the auto-submitting form that hands the customer off to PayHere's sandbox checkout. */
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('customer');

$user = current_user();
$orderId = (int) ($_GET['order'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ? AND status = 'awaiting_payment'");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require APP_ROOT . '/404.php';
    exit;
}

$amount = number_format((float) $order['total'], 2, '.', '');
$hash = strtoupper(md5(
    PAYHERE_MERCHANT_ID . $order['order_no'] . $amount . 'LKR' . strtoupper(md5(PAYHERE_MERCHANT_SECRET))
));

[$firstName, $lastName] = array_pad(explode(' ', $user['name'], 2), 2, '');

$pageTitle = 'Redirecting to payment';
require APP_ROOT . '/includes/header.php';
?>
<div class="container py-5 text-center">
  <p class="text-muted">Redirecting you to PayHere's secure sandbox checkout&hellip;</p>
  <form id="payhereForm" method="post" action="<?= PAYHERE_CHECKOUT_URL ?>">
    <input type="hidden" name="merchant_id" value="<?= e(PAYHERE_MERCHANT_ID) ?>">
    <input type="hidden" name="return_url" value="<?= url('order-confirmation.php?order=' . $order['id']) ?>">
    <input type="hidden" name="cancel_url" value="<?= url('checkout.php') ?>">
    <input type="hidden" name="notify_url" value="<?= url('payhere/notify.php') ?>">
    <input type="hidden" name="order_id" value="<?= e($order['order_no']) ?>">
    <input type="hidden" name="items" value="MediQuick Pharmacy order <?= e($order['order_no']) ?>">
    <input type="hidden" name="currency" value="LKR">
    <input type="hidden" name="amount" value="<?= $amount ?>">
    <input type="hidden" name="first_name" value="<?= e($firstName) ?>">
    <input type="hidden" name="last_name" value="<?= e($lastName) ?>">
    <input type="hidden" name="email" value="<?= e($user['email']) ?>">
    <input type="hidden" name="phone" value="<?= e($order['contact_phone']) ?>">
    <input type="hidden" name="address" value="<?= e($order['delivery_address']) ?>">
    <input type="hidden" name="city" value="<?= e($order['delivery_city']) ?>">
    <input type="hidden" name="country" value="Sri Lanka">
    <input type="hidden" name="hash" value="<?= $hash ?>">
    <noscript><button type="submit" class="btn btn-lg" style="background:var(--brand);color:#fff">Continue to payment</button></noscript>
  </form>
  <div class="spinner-border text-success mt-3" role="status"><span class="visually-hidden">Loading…</span></div>
</div>
<script>document.getElementById('payhereForm').submit();</script>
<?php require APP_ROOT . '/includes/footer.php'; ?>
