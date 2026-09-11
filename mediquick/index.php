<?php
require_once __DIR__ . '/includes/bootstrap.php';

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$featured = $pdo->query(
    "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8"
)->fetchAll();
$testimonials = $pdo->query(
    "SELECT t.*, u.full_name FROM testimonials t JOIN users u ON u.id = t.customer_id
     WHERE t.is_approved = 1 ORDER BY t.created_at DESC LIMIT 3"
)->fetchAll();

$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="display-5">Your neighbourhood pharmacy, now online</h1>
        <p class="lead text-white-50">Prescription medicines, OTC essentials and wellness products, delivered island-wide from our Kurunegala branch.</p>
        <a href="<?= url('shop.php') ?>" class="btn btn-lg mt-2" style="background:var(--brand-light);color:#04241a">Shop now</a>
        <a href="<?= url('prescription-upload.php') ?>" class="btn btn-lg btn-outline-light mt-2">Upload prescription</a>
      </div>
    </div>
  </div>
</section>

<div class="container py-5">
  <h2 class="h4 fw-bold mb-3">Browse by category</h2>
  <div class="row g-3 mb-5">
    <?php foreach ($categories as $cat): ?>
      <div class="col-6 col-md-3">
        <a class="category-card d-block text-decoration-none p-3 text-center" href="<?= url('shop.php?category=' . $cat['id']) ?>">
          <div class="category-icon">
            <i class="fa-solid <?= $cat['slug'] === 'prescription' ? 'fa-file-prescription' : ($cat['slug'] === 'otc' ? 'fa-pills' : ($cat['slug'] === 'wellness' ? 'fa-vial-circle-check' : 'fa-pump-soap')) ?>"></i>
          </div>
          <div class="fw-semibold text-dark small"><?= e($cat['name']) ?></div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="h4 fw-bold mb-3">Recommended for you</h2>
  <div class="row g-3 mb-5">
    <?php foreach ($featured as $p): ?>
      <div class="col-6 col-md-3">
        <div class="product-card">
          <a href="<?= url('product.php?id=' . $p['id']) ?>" class="text-decoration-none text-dark">
            <div class="product-thumb"><i class="fa-solid fa-pills"></i></div>
            <div class="p-2">
              <?php if ($p['requires_prescription']): ?><span class="badge badge-rx mb-1">Rx</span><?php endif; ?>
              <div class="small fw-semibold"><?= e($p['name']) ?></div>
              <div class="text-brand fw-bold"><?= money((float) $p['price']) ?></div>
            </div>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="h4 fw-bold mb-3">Why trust MediQuick?</h2>
  <div class="row g-3 mb-5">
    <div class="col-md-4"><div class="kpi-tile"><div class="fs-4">🔒</div><div class="fw-semibold mt-2">Verified prescriptions</div><p class="text-muted small mb-0">Every Rx-only order is checked by a registered pharmacist before it ships.</p></div></div>
    <div class="col-md-4"><div class="kpi-tile"><div class="fs-4">🚚</div><div class="fw-semibold mt-2">Island-wide delivery</div><p class="text-muted small mb-0">Free delivery over LKR 5,000, flat LKR 350 below that.</p></div></div>
    <div class="col-md-4"><div class="kpi-tile"><div class="fs-4">💬</div><div class="fw-semibold mt-2">Real pharmacist support</div><p class="text-muted small mb-0">Ask questions through our contact form and get a real reply.</p></div></div>
  </div>

  <?php if ($testimonials): ?>
    <h2 class="h4 fw-bold mb-3">What our customers say</h2>
    <div class="row g-3">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-md-4">
          <div class="kpi-tile">
            <div class="text-warning"><?= str_repeat('★', (int) $t['rating']) . str_repeat('☆', 5 - (int) $t['rating']) ?></div>
            <p class="mb-1">"<?= e($t['message']) ?>"</p>
            <div class="small text-muted">— <?= e($t['full_name']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
