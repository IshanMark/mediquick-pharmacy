<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('customer');

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $doctorName = trim($_POST['doctor_name'] ?? '');
    $doctorReg = trim($_POST['doctor_reg_no'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($doctorName === '') $errors[] = "Doctor's name is required.";
    if ($doctorReg === '') $errors[] = 'SLMC registration number is required.';

    if (empty($_FILES['rx_file']) || $_FILES['rx_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please choose a file to upload.';
    } elseif ($_FILES['rx_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload failed. Please try again.';
    } else {
        $file = $_FILES['rx_file'];
        if ($file['size'] > MAX_RX_FILE_BYTES) {
            $errors[] = 'File is larger than 5 MB.';
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, RX_ALLOWED_MIME, true)) {
            $errors[] = 'Only JPG, PNG or PDF files are allowed.';
        }
    }

    if (!$errors) {
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            default      => 'pdf',
        };
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = APP_ROOT . '/uploads/rx/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = 'Could not save the uploaded file. Please try again.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO prescriptions (customer_id, file_path, doctor_name, doctor_reg_no, notes)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$user['id'], $filename, $doctorName, $doctorReg, $notes ?: null]);
            log_action($pdo, $user['id'], 'prescription.upload', 'prescription', (int) $pdo->lastInsertId());
            flash('success', 'Prescription uploaded. A pharmacist will review it shortly.');
            redirect('account/prescriptions.php');
        }
    }
}

$pageTitle = 'Upload prescription';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4" style="max-width:640px">
  <h1 class="h3 fw-bold">Upload doctor's prescription</h1>
  <p class="text-muted">Required for prescription-only medicines. A pharmacist checks every upload before your order ships.</p>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger small"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label small" for="doctor_name">Doctor's name</label>
      <input id="doctor_name" name="doctor_name" class="form-control" required value="<?= e($_POST['doctor_name'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small" for="doctor_reg_no">Doctor's SLMC registration number</label>
      <input id="doctor_reg_no" name="doctor_reg_no" class="form-control" required value="<?= e($_POST['doctor_reg_no'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label small" for="rx_file">Prescription file (JPG, PNG or PDF, max 5 MB)</label>
      <input id="rx_file" name="rx_file" type="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
    </div>
    <div class="mb-3">
      <label class="form-label small" for="notes">Notes for the pharmacist (optional)</label>
      <textarea id="notes" name="notes" class="form-control" rows="2"><?= e($_POST['notes'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn w-100" style="background:var(--brand);color:#fff">Submit prescription</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
