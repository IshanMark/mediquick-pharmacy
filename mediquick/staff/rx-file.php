<?php
/** Serves an uploaded prescription only to its owner or to staff/admin — never directly from /uploads/. */
require_once __DIR__ . '/../includes/bootstrap.php';

$user = current_user();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM prescriptions WHERE id = ?');
$stmt->execute([$id]);
$rx = $stmt->fetch();

if (!$rx) {
    http_response_code(404);
    exit;
}

$isOwner = $user && $user['role'] === 'customer' && $user['id'] === (int) $rx['customer_id'];
$isStaff = $user && in_array($user['role'], ['staff', 'admin'], true);

if (!$isOwner && !$isStaff) {
    http_response_code(403);
    exit;
}

$path = APP_ROOT . '/uploads/rx/' . basename($rx['file_path']);
if (!is_file($path)) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
header('Content-Type: ' . $finfo->file($path));
header('Content-Disposition: inline; filename="' . basename($rx['file_path']) . '"');
readfile($path);
