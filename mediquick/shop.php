<?php
require_once __DIR__ . '/includes/bootstrap.php';

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$search     = trim($_GET['q'] ?? '');
$rxFilter   = $_GET['rx'] ?? '';       // '', 'rx', 'otc'
$maxPrice   = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;
$inStock    = isset($_GET['in_stock']);
$sort       = $_GET['sort'] ?? 'name';
$page       = max(1, (int) ($_GET['page'] ?? 1));
$perPage    = 12;

$where = ['is_active = 1'];
$params = [];

if ($categoryId > 0) {
    $where[] = 'category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $where[] = '(name LIKE ? OR generic_name LIKE ? OR brand LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($rxFilter === 'rx') {
    $where[] = 'requires_prescription = 1';
} elseif ($rxFilter === 'otc') {
    $where[] = 'requires_prescription = 0';
}
if ($maxPrice !== null) {
    $where[] = 'price <= ?';
    $params[] = $maxPrice;
}
if ($inStock) {
    $where[] = 'stock_qty > 0';
}

$orderBy = match ($sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    default      => 'name ASC',
};

$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM products WHERE $whereSql ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

function keep(array $extra = []): string
{
    $q = array_merge($_GET, $extra);
    return http_build_query($q);
}

$pageTitle = 'Shop';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-1">Shop healthcare products</h1>
  <p class="text-muted mb-4">Search by brand or generic name, or filter by category and price.</p>

  <form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-md-4">
      <label class="form-label small">Search</label>
      <input type="search" name="q" class="form-control" placeholder="e.g. Amoxicillin" value="<?= e($search) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small">Category</label>
      <select name="category" class="form-select">
        <option value="0">All</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Type</label>
      <select name="rx" class="form-select">
        <option value="" <?= $rxFilter === '' ? 'selected' : '' ?>>All</option>
        <option value="rx" <?= $rxFilter === 'rx' ? 'selected' : '' ?>>Prescription only</option>
        <option value="otc" <?= $rxFilter === 'otc' ? 'selected' : '' ?>>Over the counter</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Max price (LKR)</label>
      <input type="number" min="0" step="1" name="max_price" class="form-control" value="<?= e($_GET['max_price'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small">Sort by</label>
      <select name="sort" class="form-select">
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
      </select>
    </div>
    <div class="col-md-8 form-check mt-2">
      <input class="form-check-input" type="checkbox" name="in_stock" id="inStock" value="1" <?= $inStock ? 'checked' : '' ?>>
      <label class="form-check-label small" for="inStock">In stock only</label>
    </div>
    <div class="col-md-4 text-end">
      <button class="btn" style="background:var(--brand);color:#fff">Apply filters</button>
    </div>
  </form>

  <p class="text-muted small"><?= $total ?> product<?= $total === 1 ? '' : 's' ?> found</p>

  <div class="row g-3">
    <?php if (!$products): ?>
      <div class="col-12"><div class="alert alert-light border">No products match those filters. Try widening your search.</div></div>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="product-card">
          <a href="<?= url('product.php?id=' . $p['id']) ?>" class="text-decoration-none text-dark">
            <div class="product-thumb"><i class="fa-solid fa-pills"></i></div>
            <div class="p-2">
              <?php if ($p['requires_prescription']): ?><span class="badge badge-rx mb-1">Rx</span><?php endif; ?>
              <div class="small fw-semibold"><?= e($p['name']) ?></div>
              <div class="text-brand fw-bold"><?= money((float) $p['price']) ?></div>
              <div class="small <?= $p['stock_qty'] > 0 ? 'text-success' : 'text-danger' ?>">
                <?= $p['stock_qty'] > 0 ? 'In stock' : 'Out of stock' ?>
              </div>
            </div>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= keep(['page' => $i]) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
