<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['update'])) {
        foreach ($_POST['qty'] ?? [] as $productId => $qty) {
            $productId = (int) $productId;
            $qty = (int) $qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }
            $stmt = $pdo->prepare('SELECT stock_qty FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $stock = (int) $stmt->fetchColumn();
            $_SESSION['cart'][$productId] = min($qty, max(1, $stock));
        }
        flash('success', 'Cart updated.');
    }

    if (isset($_POST['remove'])) {
        unset($_SESSION['cart'][(int) $_POST['remove']]);
        flash('success', 'Item removed from cart.');
    }

    redirect('cart.php');
}

$items = cart_items($pdo);
$subtotal = array_sum(array_column($items, 'line_total'));
$deliveryFee = $subtotal > 0 && $subtotal < FREE_DELIVERY_OVER ? DELIVERY_FEE : 0.0;
$total = $subtotal + $deliveryFee;
$needsRx = cart_needs_prescription($pdo);

$pageTitle = 'Your cart';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-4">Your cart</h1>

  <?php if (!$items): ?>
    <div class="alert alert-light border">Your cart is empty. <a href="<?= url('shop.php') ?>">Continue shopping</a>.</div>
  <?php else: ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="table-responsive mb-4">
        <table class="table align-middle">
          <thead><tr><th>Product</th><th>Price</th><th style="width:110px">Qty</th><th>Line total</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <a href="<?= url('product.php?id=' . $item['id']) ?>" class="text-decoration-none text-dark fw-semibold"><?= e($item['name']) ?></a>
                <?php if ($item['requires_prescription']): ?><span class="badge badge-rx ms-1">Rx</span><?php endif; ?>
              </td>
              <td><?= money((float) $item['price']) ?></td>
              <td><input type="number" name="qty[<?= $item['id'] ?>]" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stock_qty'] ?>" class="form-control form-control-sm"></td>
              <td class="fw-semibold"><?= money($item['line_total']) ?></td>
              <td><button type="submit" name="remove" value="<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger">Remove</button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" name="update" value="1" class="btn btn-outline-secondary mb-4">Update cart</button>
    </form>

    <div class="row justify-content-end">
      <div class="col-md-4">
        <div class="kpi-tile">
          <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
          <div class="d-flex justify-content-between small mb-2"><span>Delivery</span><span><?= $deliveryFee > 0 ? money($deliveryFee) : 'Free' ?></span></div>
          <div class="d-flex justify-content-between fw-bold h5"><span>Total</span><span><?= money($total) ?></span></div>

          <?php if ($needsRx): ?>
            <div class="alert alert-warning small mt-3 mb-2">
              Your cart contains a prescription-only medicine. You'll need to attach an approved prescription at checkout.
            </div>
          <?php endif; ?>

          <a href="<?= url('checkout.php') ?>" class="btn w-100 mt-2" style="background:var(--brand);color:#fff">Proceed to checkout</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
