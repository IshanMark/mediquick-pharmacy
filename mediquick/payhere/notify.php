<?php
/**
 * PayHere server-to-server callback. This is the ONLY thing that ever confirms a card
 * payment — the customer's browser returning to return_url is not trusted for that.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$merchantId   = $_POST['merchant_id']   ?? '';
$orderNo      = $_POST['order_id']      ?? '';
$amount       = $_POST['payhere_amount'] ?? '';
$currency     = $_POST['payhere_currency'] ?? '';
$statusCode   = $_POST['status_code']   ?? '';
$md5sig       = $_POST['md5sig']        ?? '';
$paymentId    = $_POST['payment_id']    ?? '';

$expectedSig = strtoupper(md5(
    $merchantId . $orderNo . $amount . $currency . $statusCode .
    strtoupper(md5(PAYHERE_MERCHANT_SECRET))
));

if (!hash_equals($expectedSig, strtoupper($md5sig))) {
    error_log("PayHere notify: signature mismatch for order $orderNo");
    http_response_code(400);
    exit('Invalid signature');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no = ? AND status = 'awaiting_payment'");
$stmt->execute([$orderNo]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

// status_code 2 = success per PayHere's documentation.
if ((int) $statusCode === 2) {
    try {
        $pdo->beginTransaction();

        $pdo->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid' WHERE id = ?")
            ->execute([$order['id']]);

        $pdo->prepare(
            "INSERT INTO payments (order_id, gateway, gateway_ref, amount, status) VALUES (?, 'payhere', ?, ?, 'success')"
        )->execute([$order['id'], $paymentId, $amount]);

        $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $items->execute([$order['id']]);
        $stockStmt = $pdo->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?');
        foreach ($items->fetchAll() as $line) {
            $stockStmt->execute([$line['quantity'], $line['product_id']]);
        }

        $pdo->commit();
        notify($pdo, $order['customer_id'], 'Payment received', "Order {$order['order_no']} is paid and confirmed.", 'account/order-detail.php?id=' . $order['id']);
        log_action($pdo, $order['customer_id'], 'payment.success', 'order', $order['id']);
    } catch (Exception $ex) {
        $pdo->rollBack();
        error_log('PayHere notify processing failed: ' . $ex->getMessage());
        http_response_code(500);
        exit('Processing error');
    }
} else {
    $pdo->prepare("UPDATE orders SET status = 'cancelled', payment_status = 'failed' WHERE id = ?")
        ->execute([$order['id']]);
    $pdo->prepare(
        "INSERT INTO payments (order_id, gateway, gateway_ref, amount, status) VALUES (?, 'payhere', ?, ?, 'failed')"
    )->execute([$order['id'], $paymentId, $amount]);
}

http_response_code(200);
echo 'OK';
