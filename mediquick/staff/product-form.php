<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('staff', 'admin');

$staff = current_user();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$id = (int) ($_GET['id'] ?? 0);
$product = ['name' => '', 'generic_name' => '', 'brand' => '', 'category_id' => $categories[0]['id'] ?? '',
    'description' => '', 'dosage_guidelines' => '', 'safety_info' => '', 'price' => '', 'stock_qty' => 0,
    'reorder_level' => 10, 'requires_prescription' => 0, 'expiry_date' => ''];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        require __DIR__ . '/../404.php';
        exit;
    }
    $product = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name = trim($_POST['name'] ?? '');
    $genericName = trim($_POST['generic_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? -1);
    $stock = (int) ($_POST['stock_qty'] ?? -1);
    $reorder = (int) ($_POST['reorder_level'] ?? 0);
    $rx = isset($_POST['requires_prescription']) ? 1 : 0;
    $expiry = trim($_POST['expiry_date'] ?? '') ?: null;
    $description = trim($_POST['description'] ?? '');
    $dosage = trim($_POST['dosage_guidelines'] ?? '');
    $safety = trim($_POST['safety_info'] ?? '');

    if ($name === '') $errors[] = 'Product name is required.';
    if ($categoryId <= 0) $errors[] = 'Choose a category.';
    if ($price < 0) $errors[] = 'Price must be zero or more.';
    if ($stock < 0) $errors[] = 'Stock must be zero or more.';

    if (!$errors) {
        if ($id) {
            $pdo->prepare(
                'UPDATE products SET name=?, generic_name=?, brand=?, category_id=?, description=?,
                 dosage_guidelines=?, safety_info=?, price=?, stock_qty=?, reorder_level=?,
                 requires_prescription=?, expiry_date=? WHERE id=?'
            )->execute([$name, $genericName ?: null, $brand ?: null, $categoryId, $description ?: null,
                $dosage ?: null, $safety ?: null, $price, $stock, $reorder, $rx, $expiry, $id]);
            log_action($pdo, $staff['id'], 'product.update', 'product', $id);
        } else {
            $pdo->prepare(
                'INSERT INTO products (category_id, name, generic_name, brand, description, dosage_guidelines,
                    safety_info, price, stock_qty, reorder_level, requires_prescription, expiry_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$categoryId, $name, $genericName ?: null, $brand ?: null, $description ?: null,
                $dosage ?: null, $safety ?: null, $price, $stock, $reorder, $rx, $expiry]);
            $id = (int) $pdo->lastInsertId();
            log_action($pdo, $staff['id'], 'product.create', 'product', $id);
        }
        flash('success', 'Product saved.');
        redirect('staff/products.php');
    }
    $product = compact('name', 'genericName', 'brand', 'categoryId', 'price', 'stock', 'reorder', 'rx', 'expiry', 'description', 'dosage', 'safety');
}

$area = 'staff'; $active = 'products'; $pageTitle = $id ? 'Edit product' : 'Add product';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="kpi-tile" style="max-width:720px">
  <?php foreach ($errors as $err): ?><div class="alert alert-danger small"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small">Product name</label>
        <input name="name" class="form-control" required value="<?= e($product['name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Generic name</label>
        <input name="generic_name" class="form-control" value="<?= e($product['generic_name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Brand</label>
        <input name="brand" class="form-control" value="<?= e($product['brand'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Category</label>
        <select name="category_id" class="form-select">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Price (LKR)</label>
        <input type="number" step="0.01" min="0" name="price" class="form-control" required value="<?= e((string) ($product['price'] ?? '')) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Stock quantity</label>
        <input type="number" min="0" name="stock_qty" class="form-control" required value="<?= e((string) ($product['stock_qty'] ?? 0)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Reorder level</label>
        <input type="number" min="0" name="reorder_level" class="form-control" value="<?= e((string) ($product['reorder_level'] ?? 10)) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Expiry date</label>
        <input type="date" name="expiry_date" class="form-control" value="<?= e($product['expiry_date'] ?? '') ?>">
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="requires_prescription" id="rx" value="1" <?= ($product['requires_prescription'] ?? 0) ? 'checked' : '' ?>>
          <label class="form-check-label" for="rx">Requires a prescription</label>
        </div>
      </div>
      <div class="col-12">
        <label class="form-label small">Description</label>
        <textarea name="description" class="form-control" rows="2"><?= e($product['description'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small">Dosage guidelines</label>
        <textarea name="dosage_guidelines" class="form-control" rows="2"><?= e($product['dosage_guidelines'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small">Safety information</label>
        <textarea name="safety_info" class="form-control" rows="2"><?= e($product['safety_info'] ?? '') ?></textarea>
      </div>
    </div>
    <button type="submit" class="btn mt-3" style="background:var(--brand);color:#fff">Save product</button>
    <a href="<?= url('staff/products.php') ?>" class="btn btn-outline-secondary mt-3">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
