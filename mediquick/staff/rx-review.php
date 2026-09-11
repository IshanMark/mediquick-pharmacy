<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$staff = current_user();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT p.*, u.full_name, u.email FROM prescriptions p JOIN users u ON u.id = p.customer_id WHERE p.id = ?'
);
$stmt->execute([$id]);
$rx = $stmt->fetch();

if (!$rx) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $decision = $_POST['decision'] ?? '';
    $note = trim($_POST['review_note'] ?? '');

    if (in_array($decision, ['approved', 'rejected'], true)) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare(
                'UPDATE prescriptions SET status = ?, reviewed_by = ?, review_note = ?, reviewed_at = NOW() WHERE id = ?'
            )->execute([$decision, $staff['id'], $note ?: null, $rx['id']]);

            // Move any order that was placed against this exact prescription and is still waiting on it.
            $ordersStmt = $pdo->prepare(
                "SELECT * FROM orders WHERE prescription_id = ? AND status = 'pending_verification'"
            );
            $ordersStmt->execute([$rx['id']]);
            $waitingOrders = $ordersStmt->fetchAll();

            foreach ($waitingOrders as $order) {
                if ($decision === 'rejected') {
                    $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$order['id']]);
                    notify($pdo, $order['customer_id'], 'Order cancelled', "Order {$order['order_no']} was cancelled because the linked prescription was rejected." . ($note !== '' ? " Reason: $note" : ''), 'account/order-detail.php?id=' . $order['id']);
                    continue;
                }

                if ($order['payment_method'] === 'cod') {
                    // Approved + cash on delivery: confirm now and deduct stock, same as a normal COD checkout.
                    $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?")->execute([$order['id']]);
                    $lineStmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
                    $lineStmt->execute([$order['id']]);
                    $stockStmt = $pdo->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?');
                    foreach ($lineStmt->fetchAll() as $line) {
                        $stockStmt->execute([$line['quantity'], $line['product_id'], $line['quantity']]);
                    }
                    notify($pdo, $order['customer_id'], 'Order confirmed', "Your prescription was approved. Order {$order['order_no']} is now confirmed for cash on delivery.", 'account/order-detail.php?id=' . $order['id']);
                } else {
                    // Approved + card: order can now be paid.
                    $pdo->prepare("UPDATE orders SET status = 'awaiting_payment' WHERE id = ?")->execute([$order['id']]);
                    notify($pdo, $order['customer_id'], 'Prescription approved — pay now', "Your prescription was approved. Pay for order {$order['order_no']} to complete it.", 'account/order-detail.php?id=' . $order['id']);
                }
            }

            $pdo->commit();
        } catch (Exception $ex) {
            $pdo->rollBack();
            error_log('Prescription review failed: ' . $ex->getMessage());
            flash('error', 'Something went wrong saving that decision. Please try again.');
            redirect('staff/rx-review.php?id=' . $rx['id']);
        }

        log_action($pdo, $staff['id'], "prescription.$decision", 'prescription', $rx['id']);

        $verb = $decision === 'approved' ? 'approved' : 'rejected';
        notify(
            $pdo, $rx['customer_id'], "Prescription $verb",
            $decision === 'approved'
                ? 'Your prescription was approved. You can now check out with your prescription items.'
                : 'Your prescription was rejected. ' . ($note !== '' ? "Reason: $note" : 'Please upload a clearer copy.'),
            'account/prescriptions.php'
        );

        flash('success', "Prescription $verb.");
        redirect('staff/rx-queue.php');
    }
}

$fileExt = pathinfo($rx['file_path'], PATHINFO_EXTENSION);

$area = 'staff'; $active = 'rx-queue'; $pageTitle = 'Review prescription';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-4">
  <div class="col-md-6">
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Uploaded file</h2>
      <?php if ($fileExt === 'pdf'): ?>
        <a href="<?= url('staff/rx-file.php?id=' . $rx['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Open PDF in new tab</a>
      <?php else: ?>
        <img src="<?= url('staff/rx-file.php?id=' . $rx['id']) ?>" class="img-fluid rounded border" alt="Uploaded prescription">
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6">
    <div class="kpi-tile mb-3">
      <h2 class="h6 fw-bold">Details</h2>
      <p class="small mb-1"><strong>Customer:</strong> <?= e($rx['full_name']) ?> (<?= e($rx['email']) ?>)</p>
      <p class="small mb-1"><strong>Doctor:</strong> <?= e($rx['doctor_name']) ?></p>
      <p class="small mb-1"><strong>SLMC reg. no.:</strong> <?= e($rx['doctor_reg_no']) ?></p>
      <p class="small mb-1"><strong>Notes from customer:</strong> <?= e($rx['notes'] ?? '—') ?></p>
      <p class="small mb-0"><strong>Uploaded:</strong> <?= date('d M Y, g:i A', strtotime($rx['uploaded_at'])) ?></p>
    </div>

    <?php if ($rx['status'] === 'pending'): ?>
      <div class="kpi-tile">
        <h2 class="h6 fw-bold">Decision</h2>
        <form method="post">
          <?= csrf_field() ?>
          <textarea name="review_note" class="form-control mb-3" rows="2" placeholder="Optional note to the customer"></textarea>
          <button type="submit" name="decision" value="approved" class="btn btn-success">Approve</button>
          <button type="submit" name="decision" value="rejected" class="btn btn-danger">Reject</button>
        </form>
      </div>
    <?php else: ?>
      <?php [$label, $badge] = rx_status_badge($rx['status']); ?>
      <div class="alert <?= str_replace('text-bg', 'alert', $badge) ?>">
        Already <?= strtolower($label) ?>.<?= $rx['review_note'] ? ' Note: ' . e($rx['review_note']) : '' ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
