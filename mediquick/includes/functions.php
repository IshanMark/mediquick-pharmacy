<?php
/** Small shared helpers used across every page. */

/** Escape for HTML output. Short name because it's used constantly in templates. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float $amount): string
{
    return 'LKR ' . number_format($amount, 2);
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function order_no(): string
{
    return 'MQ-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

/** Cart lives in the session as [product_id => quantity]. */
function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(cart());
}

/** Loads full product rows for everything currently in the cart. */
function cart_items(PDO $pdo): array
{
    $cart = cart();
    if (!$cart) {
        return [];
    }
    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $items = [];
    foreach ($stmt->fetchAll() as $product) {
        $qty = $cart[$product['id']] ?? 0;
        if ($qty > 0) {
            $product['qty'] = $qty;
            $product['line_total'] = $qty * (float) $product['price'];
            $items[] = $product;
        }
    }
    return $items;
}

function cart_needs_prescription(PDO $pdo): bool
{
    foreach (cart_items($pdo) as $item) {
        if ((int) $item['requires_prescription'] === 1) {
            return true;
        }
    }
    return false;
}

function unread_notification_count(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function notify(PDO $pdo, int $userId, string $title, string $message, ?string $link = null): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $title, $message, $link]);
}

function log_action(PDO $pdo, ?int $userId, string $action, ?string $entity = null, ?int $entityId = null): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $action, $entity, $entityId, $_SERVER['REMOTE_ADDR'] ?? null]);
}

/** Human-readable label + Bootstrap badge class for an order status. */
function order_status_badge(string $status): array
{
    return match ($status) {
        'pending_verification' => ['Awaiting prescription check', 'text-bg-warning'],
        'awaiting_payment'     => ['Awaiting payment', 'text-bg-info'],
        'confirmed'            => ['Confirmed', 'text-bg-primary'],
        'packed'               => ['Packed', 'text-bg-secondary'],
        'dispatched'           => ['Dispatched', 'text-bg-secondary'],
        'delivered'            => ['Delivered', 'text-bg-success'],
        'cancelled'            => ['Cancelled', 'text-bg-danger'],
        default                => [ucfirst($status), 'text-bg-light'],
    };
}

function rx_status_badge(string $status): array
{
    return match ($status) {
        'pending'  => ['Pending review', 'text-bg-warning'],
        'approved' => ['Approved', 'text-bg-success'],
        'rejected' => ['Rejected', 'text-bg-danger'],
        default    => [ucfirst($status), 'text-bg-light'],
    };
}
