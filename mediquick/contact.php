<?php
require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name = trim($_POST['name'] ?? ($user['name'] ?? ''));
    $email = trim($_POST['email'] ?? ($user['email'] ?? ''));
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($subject === '') $errors[] = 'Subject is required.';
    if ($message === '') $errors[] = 'Message is required.';

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO inquiries (customer_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$user['id'] ?? null, $name, $email, $subject, $message]);
        $sent = true;
    }
}

$pageTitle = 'Contact us';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-1">Contact us &amp; branch locations</h1>
  <p class="text-muted mb-4">Questions about an order or a medicine? Send us a message and a pharmacist will reply.</p>

  <div class="row g-4">
    <div class="col-md-7">
      <?php if ($sent): ?>
        <div class="alert alert-success">Thanks — your message has been sent. We'll reply within one business day.</div>
      <?php endif; ?>
      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger small"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post">
        <?= csrf_field() ?>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label small" for="name">Name</label>
            <input id="name" name="name" class="form-control" required value="<?= e($_POST['name'] ?? ($user['name'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small" for="email">Email</label>
            <input id="email" name="email" type="email" class="form-control" required value="<?= e($_POST['email'] ?? ($user['email'] ?? '')) ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small" for="subject">Subject</label>
          <input id="subject" name="subject" class="form-control" required value="<?= e($_POST['subject'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label small" for="message">Message</label>
          <textarea id="message" name="message" class="form-control" rows="4" required><?= e($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn" style="background:var(--brand);color:#fff">Send message</button>
      </form>
    </div>
    <div class="col-md-5">
      <div class="kpi-tile">
        <h2 class="h6 fw-bold">Kurunegala branch</h2>
        <p class="small text-muted mb-1">No. 12, Kandy Road, Kurunegala</p>
        <p class="small text-muted mb-1">Open daily, 8:00 AM – 9:00 PM</p>
        <p class="small text-muted mb-1">☎ 037 222 4455</p>
        <p class="small text-muted mb-0">✉ care@mediquick.lk</p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
