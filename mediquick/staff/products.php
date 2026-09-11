<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$staff = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    csrf_check();
    $id = (int) $_POST['toggle_active'];
    $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
    log_action($pdo, $staff['id'], 'product.toggle', 'product', $id);
    redirect('staff/products.php');
}

$search = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? '';

$where = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.generic_name LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filter === 'low_stock') $where[] = 'stock_qty <= reorder_level';
if ($filter === 'expiring') $where[] = 'expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)';
if ($filter === 'inactive') $where[] = 'is_active = 0';

$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id
     WHERE ' . implode(' AND ', $where) . ' ORDER BY p.name LIMIT 200'
);
$stmt->execute($params);
$products = $stmt->fetchAll();

$area = 'staff'; $active = 'products'; $pageTitle = 'Products & stock';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form method="get" class="d-flex gap-2">
    <input type="search" name="q" class="form-control" placeholder="Search products" value="<?= e($search) ?>">
    <select name="filter" class="form-select" style="width:auto">
      <option value="">All</option>
      <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low stock</option>
      <option value="expiring" <?= $filter === 'expiring' ? 'selected' : '' ?>>Expiring in 90 days</option>
      <option value="inactive" <?= $filter === 'inactive' ? 'selected' : '' ?>>Deactivated</option>
    </select>
    <button class="btn btn-outline-secondary">Filter</button>
  </form>
  <a href="<?= url('staff/product-form.php') ?>" class="btn" style="background:var(--brand);color:#fff">+ Add product</a>
</div>

<div class="kpi-tile">
  <?php if (!$products): ?>
    <p class="text-muted mb-0">No products match.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Expiry</th><th>Rx</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= e($p['name']) ?></td>
          <td><?= e($p['category_name']) ?></td>
          <td><?= money((float) $p['price']) ?></td>
          <td class="<?= $p['stock_qty'] <= $p['reorder_level'] ? 'text-danger fw-semibold' : '' ?>"><?= $p['stock_qty'] ?></td>
          <td><?= $p['expiry_date'] ? date('d M Y', strtotime($p['expiry_date'])) : '—' ?></td>
          <td><?= $p['requires_prescription'] ? '<span class="badge badge-rx">Rx</span>' : 'OTC' ?></td>
          <td><span class="badge <?= $p['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td class="d-flex gap-2">
            <a href="<?= url('staff/product-form.php?id=' . $p['id']) ?>" class="small">Edit</a>
            <form method="post" onsubmit="return confirm('<?= $p['is_active'] ? 'Deactivate' : 'Reactivate' ?> this product?')">
              <?= csrf_field() ?>
              <button type="submit" name="toggle_active" value="<?= $p['id'] ?>" class="btn btn-link btn-sm p-0 small"><?= $p['is_active'] ? 'Deactivate' : 'Reactivate' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
