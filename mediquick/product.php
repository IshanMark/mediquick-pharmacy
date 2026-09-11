<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ? AND p.is_active = 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    csrf_check();
    $qty = max(1, min((int) $_POST['qty'], (int) $product['stock_qty']));
    $_SESSION['cart'][$product['id']] = ($_SESSION['cart'][$product['id']] ?? 0) + $qty;
    flash('success', e($product['name']) . ' added to cart.');
    redirect('product.php?id=' . $product['id']);
}

$related = $pdo->prepare('SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 4');
$related->execute([$product['category_id'], $product['id']]);
$relatedProducts = $related->fetchAll();

$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <p class="small"><a href="<?= url('shop.php') ?>">Shop</a> / <a href="<?= url('shop.php?category=' . $product['category_id']) ?>"><?= e($product['category_name']) ?></a></p>

  <div class="row g-4">
    <div class="col-md-5">
      <div class="product-thumb" style="border-radius:.75rem;font-size:5rem;aspect-ratio:1;"><i class="fa-solid fa-pills"></i></div>
    </div>
    <div class="col-md-7">
      <?php if ($product['requires_prescription']): ?>
        <span class="badge badge-rx mb-2">Prescription required</span>
      <?php endif; ?>
      <h1 class="h3 fw-bold"><?= e($product['name']) ?></h1>
      <p class="text-muted mb-1"><?= e($product['brand'] ?? '') ?> <?= $product['generic_name'] ? '· ' . e($product['generic_name']) : '' ?></p>
      <div class="h4 text-brand fw-bold mb-3"><?= money((float) $product['price']) ?></div>

      <p class="<?= $product['stock_qty'] > 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
        <?= $product['stock_qty'] > 0 ? $product['stock_qty'] . ' in stock' : 'Out of stock' ?>
      </p>

      <?php if ($product['stock_qty'] > 0): ?>
        <form method="post" class="d-flex gap-2 align-items-center mb-4">
          <?= csrf_field() ?>
          <input type="number" name="qty" value="1" min="1" max="<?= $product['stock_qty'] ?>" class="form-control" style="width:90px">
          <button type="submit" name="add_to_cart" value="1" class="btn" style="background:var(--brand);color:#fff">Add to cart</button>
        </form>
      <?php endif; ?>

      <?php if ($product['requires_prescription']): ?>
        <div class="alert alert-warning small">
          This is a prescription-only medicine. You'll need to upload a valid prescription before checkout.
          <a href="<?= url('prescription-upload.php') ?>">Upload now</a>.
        </div>
      <?php endif; ?>

      <?php if ($product['description']): ?>
        <h2 class="h6 fw-bold mt-4">Description</h2>
        <p><?= nl2br(e($product['description'])) ?></p>
      <?php endif; ?>
      <?php if ($product['dosage_guidelines']): ?>
        <h2 class="h6 fw-bold mt-3">Dosage guidelines</h2>
        <p><?= nl2br(e($product['dosage_guidelines'])) ?></p>
      <?php endif; ?>
      <?php if ($product['safety_info']): ?>
        <h2 class="h6 fw-bold mt-3">Safety information</h2>
        <p><?= nl2br(e($product['safety_info'])) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($relatedProducts): ?>
    <h2 class="h5 fw-bold mt-5 mb-3">You may also need</h2>
    <div class="row g-3">
      <?php foreach ($relatedProducts as $rp): ?>
        <div class="col-6 col-md-3">
          <div class="product-card">
            <a href="<?= url('product.php?id=' . $rp['id']) ?>" class="text-decoration-none text-dark">
              <div class="product-thumb"><i class="fa-solid fa-pills"></i></div>
              <div class="p-2">
                <div class="small fw-semibold"><?= e($rp['name']) ?></div>
                <div class="text-brand fw-bold"><?= money((float) $rp['price']) ?></div>
              </div>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
