<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('customer');

$user = current_user();
$items = cart_items($pdo);

if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('shop.php');
}

$needsRx = cart_needs_prescription($pdo);

// A pending prescription is attachable too: the order is created straight away and held in
// pending_verification until a pharmacist reviews it (see staff/rx-review.php), matching FR-14/FR-15.
// A prescription authorises exactly one live order — once it's attached to an order that isn't
// cancelled, it can't be reused for a second order. Ordering the same medicine again needs a fresh upload.
$usableRx = [];
if ($needsRx) {
    $stmt = $pdo->prepare(
        "SELECT p.* FROM prescriptions p WHERE p.customer_id = ? AND p.status IN ('pending','approved')
         AND NOT EXISTS (
             SELECT 1 FROM orders o WHERE o.prescription_id = p.id AND o.status != 'cancelled'
         )
         ORDER BY p.uploaded_at DESC"
    );
    $stmt->execute([$user['id']]);
    $usableRx = $stmt->fetchAll();
}

$subtotal = array_sum(array_column($items, 'line_total'));
$deliveryFee = $subtotal < FREE_DELIVERY_OVER ? DELIVERY_FEE : 0.0;
$total = $subtotal + $deliveryFee;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';
    $prescriptionId = (int) ($_POST['prescription_id'] ?? 0);

    if ($address === '') $errors[] = 'Delivery address is required.';
    if ($city === '') $errors[] = 'City is required.';
    if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Enter a valid contact phone number.';
    if (!in_array($paymentMethod, ['card', 'cod'], true)) $errors[] = 'Choose a payment method.';

    $attachedRx = null;
    if ($needsRx) {
        $matches = array_values(array_filter($usableRx, fn($rx) => (int) $rx['id'] === $prescriptionId));
        if (!$matches) {
            $errors[] = 'Attach a prescription before checking out with a prescription-only item.';
        } else {
            $attachedRx = $matches[0];
        }
    } else {
        $prescriptionId = null;
    }

    // Re-check stock right before committing, in case it changed since the cart page loaded.
    foreach ($items as $item) {
        if ($item['qty'] > (int) $item['stock_qty']) {
            $errors[] = e($item['name']) . ' no longer has enough stock.';
        }
    }

    if (!$errors) {
        $orderNo = order_no();
        if ($attachedRx && $attachedRx['status'] === 'pending') {
            // Prescription not reviewed yet: the order waits, untouched, until staff decide (FR-15).
            $status = 'pending_verification';
        } else {
            $status = $paymentMethod === 'cod' ? 'confirmed' : 'awaiting_payment';
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO orders (order_no, customer_id, prescription_id, status, payment_method,
                    payment_status, subtotal, delivery_fee, total, delivery_address, delivery_city, contact_phone)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderNo, $user['id'], $prescriptionId, $status, $paymentMethod,
                'unpaid', $subtotal, $deliveryFee, $total, $address, $city, $phone,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
            );
            $stockStmt = $pdo->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?');
            foreach ($items as $item) {
                $itemStmt->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
                // Stock is only deducted once the order is confirmed (COD here; card confirms on payhere callback).
                if ($status === 'confirmed') {
                    $stockStmt->execute([$item['qty'], $item['id'], $item['qty']]);
                }
            }

            $pdo->commit();
        } catch (Exception $ex) {
            $pdo->rollBack();
            error_log('Checkout failed: ' . $ex->getMessage());
            $errors[] = 'Something went wrong placing your order. Please try again.';
        }

        if (!$errors) {
            log_action($pdo, $user['id'], 'order.create', 'order', $orderId);
            $_SESSION['cart'] = [];

            if ($status === 'pending_verification') {
                notify($pdo, $user['id'], 'Order placed', "Order $orderNo is waiting on a pharmacist to review your prescription before it can proceed.", 'account/order-detail.php?id=' . $orderId);
                redirect('order-confirmation.php?order=' . $orderId);
            }

            if ($paymentMethod === 'cod') {
                notify($pdo, $user['id'], 'Order placed', "Order $orderNo is confirmed for cash on delivery.", 'account/order-detail.php?id=' . $orderId);
                redirect('order-confirmation.php?order=' . $orderId);
            }

            // Card: hand off to the PayHere sandbox checkout page.
            redirect('payhere/pay.php?order=' . $orderId);
        }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-4">Review cart &amp; delivery details</h1>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger small"><?= e($err) ?></div>
  <?php endforeach; ?>

  <div class="row g-4">
    <div class="col-md-7">
      <form method="post">
        <?= csrf_field() ?>
        <h2 class="h6 fw-bold">Delivery address</h2>
        <div class="mb-3">
          <label class="form-label small" for="address">Street address</label>
          <input id="address" name="address" class="form-control" required value="<?= e($_POST['address'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label small" for="city">City</label>
          <input id="city" name="city" class="form-control" required value="<?= e($_POST['city'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label small" for="phone">Contact phone</label>
          <input id="phone" name="phone" class="form-control" required pattern="[0-9+\-\s]{7,15}" value="<?= e($_POST['phone'] ?? '') ?>">
        </div>

        <?php if ($needsRx): ?>
          <h2 class="h6 fw-bold mt-4">Prescription</h2>
          <?php if (!$usableRx): ?>
            <div class="alert alert-warning small">
              You have no prescription available for a new order (a prescription can only be used once).
              <a href="<?= url('prescription-upload.php') ?>">Upload one</a>, then come back here to place your order — a pharmacist will review it before it ships.
            </div>
          <?php else: ?>
            <select name="prescription_id" class="form-select mb-2" required>
              <option value="">Choose a prescription</option>
              <?php foreach ($usableRx as $rx): [$rxLabel] = rx_status_badge($rx['status']); ?>
                <option value="<?= $rx['id'] ?>"><?= e($rx['doctor_name'] ?? 'Prescription') ?> — uploaded <?= date('d M Y', strtotime($rx['uploaded_at'])) ?> (<?= $rxLabel ?>)</option>
              <?php endforeach; ?>
            </select>
            <p class="small text-muted mb-3">If you pick a prescription that's still pending review, your order is placed now but held until a pharmacist approves it — you won't be charged until then.</p>
          <?php endif; ?>
        <?php endif; ?>

        <h2 class="h6 fw-bold mt-4">Payment method</h2>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment_method" value="card" id="payCard" checked>
          <label class="form-check-label" for="payCard">Card (PayHere sandbox)</label>
        </div>
        <div class="form-check mb-4">
          <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payCod">
          <label class="form-check-label" for="payCod">Cash on delivery</label>
        </div>

        <button type="submit" class="btn w-100" style="background:var(--brand);color:#fff" <?= $needsRx && !$usableRx ? 'disabled' : '' ?>>Place order</button>
      </form>
    </div>
    <div class="col-md-5">
      <div class="kpi-tile">
        <h2 class="h6 fw-bold">Order summary</h2>
        <?php foreach ($items as $item): ?>
          <div class="d-flex justify-content-between small mb-1">
            <span><?= e($item['name']) ?> × <?= $item['qty'] ?></span>
            <span><?= money($item['line_total']) ?></span>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
        <div class="d-flex justify-content-between small mb-2"><span>Delivery</span><span><?= $deliveryFee > 0 ? money($deliveryFee) : 'Free' ?></span></div>
        <div class="d-flex justify-content-between fw-bold h5"><span>Total</span><span><?= money($total) ?></span></div>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
