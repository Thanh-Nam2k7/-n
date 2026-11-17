<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

// Fallback Unicode cơ bản khi mbstring bị tắt (nên bật mbstring trong XAMPP).
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $text, ?string $encoding = null): int { return preg_match_all('/./us', $text) ?: 0; }
}
if (!function_exists('mb_substr')) {
    function mb_substr(string $text, int $start, ?int $length = null, ?string $encoding = null): string {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return implode('', array_slice($chars, $start, $length));
    }
}
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper(string $text, ?string $encoding = null): string { return strtoupper($text); }
}

if (session_status() === PHP_SESSION_NONE) {
    session_name('H2SO4_SESSID');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'samesite' => 'Lax']);
    session_start();
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Tự dò đường dẫn, chạy được tại localhost/H2SO4-PHONES hoặc virtual host riêng.
$folder = basename(dirname(__DIR__));
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$found = preg_match('~^(.*?)/' . preg_quote($folder, '~') . '(?:/|$)~', $script, $matches);
define('APP_BASE', $found ? rtrim($matches[1] . '/' . $folder, '/') : '');

function url(string $path = ''): string { return APP_BASE . '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money(int|float|string $n): string { return number_format((float)$n, 0, ',', '.') . '₫'; }
function cut(string $value, int $length = 85): string {
    return mb_strlen($value, 'UTF-8') > $length ? mb_substr($value, 0, $length, 'UTF-8') . '…' : $value;
}
function db(): PDO {
    static $pdo;
    if (!isset($pdo)) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]);
        } catch (PDOException $ex) {
            error_log('H2SO4 DB: ' . $ex->getMessage());
            http_response_code(503);
            exit('<!doctype html><html lang="vi"><meta charset="utf-8"><title>Chưa kết nối được MySQL</title><body style="font:17px Arial;background:#0c1019;color:#edf5ff;padding:50px;max-width:750px;margin:auto"><h1>Không kết nối được MySQL</h1><p>Hãy bật MySQL trong XAMPP, tạo database <b>h2so4_phones</b>, import file <code>database/h2so4_phones.sql</code> qua phpMyAdmin và kiểm tra cấu hình <code>app/config.php</code>.</p><p><a style="color:#65e4cf" href="http://localhost/">Kiểm tra XAMPP</a></p></body></html>');
        }
    }
    return $pdo;
}
function query(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql); $stmt->execute($params); return $stmt;
}
function one(string $sql, array $params = []): ?array { $row = query($sql, $params)->fetch(); return $row ?: null; }
function all(string $sql, array $params = []): array { return query($sql, $params)->fetchAll(); }
function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
function flash_messages(): array { $msgs = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $msgs; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function check_csrf(): void {
    if (empty($_POST['_csrf']) || !hash_equals(csrf_token(), (string)$_POST['_csrf'])) { http_response_code(419); exit('Phiên làm việc không hợp lệ. Vui lòng tải lại trang và thử lại.'); }
}
function post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function user(): ?array {
    static $memo = false;
    if ($memo !== false) return $memo;
    $id = (int)($_SESSION['user_id'] ?? 0);
    $memo = $id ? one('SELECT id, name, email, phone, role, created_at FROM users WHERE id=? AND is_active=1', [$id]) : null;
    if ($id && !$memo) unset($_SESSION['user_id']);
    return $memo;
}
function require_login(): array { if (!($u = user())) { flash('error', 'Vui lòng đăng nhập để tiếp tục.'); redirect('login.php'); } return $u; }
function require_admin(): array { $u = require_login(); if ($u['role'] !== 'admin') { http_response_code(403); exit('403 - Bạn không có quyền truy cập.'); } return $u; }
function cart_items(): array {
    $basket = $_SESSION['cart'] ?? [];
    if (!$basket) return [];
    $ids = array_filter(array_map('intval', array_keys($basket)), fn($i) => $i > 0);
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = all("SELECT id, name, slug, image, price, old_price, stock, is_active FROM products WHERE id IN ($placeholders)", array_values($ids));
    foreach ($rows as &$row) { $row['qty'] = min(max(1, (int)($basket[$row['id']] ?? 1)), 99); $row['total'] = $row['qty'] * (int)$row['price']; }
    unset($row);
    return $rows;
}
function cart_count(): int { return array_sum(array_map('intval', $_SESSION['cart'] ?? [])); }
function cart_subtotal(array $items): int { return array_sum(array_column($items, 'total')); }
function shipping_cost(int $subtotal): int { return $subtotal >= FREE_SHIPPING_FROM ? 0 : SHIPPING_FEE; }
function nav_categories(): array { return all('SELECT id, name, slug, icon FROM categories WHERE is_active=1 ORDER BY sort_order ASC, id ASC'); }
function product_image(?string $path): string { return url(($path && !str_contains($path, '..') && !preg_match('~^https?://~', $path)) ? $path : 'assets/images/products/default.svg'); }
function format_order_status(string $value): string { return ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','completed'=>'Hoàn tất','cancelled'=>'Đã hủy'][$value] ?? $value; }
function is_wished(int $productId): bool { if (!user()) return false; return (bool)one('SELECT id FROM wishlists WHERE user_id=? AND product_id=?', [(int)user()['id'], $productId]); }
function upload_product_image(string $key, ?string $old = null): ?string {
    if (empty($_FILES[$key]['name'])) return $old;
    $file = $_FILES[$key];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3 * 1024 * 1024) throw new RuntimeException('Ảnh không hợp lệ hoặc vượt 3 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($extensions[$mime])) throw new RuntimeException('Chỉ chấp nhận JPG, PNG hoặc WebP.');
    if (@getimagesize($file['tmp_name']) === false) throw new RuntimeException('File tải lên không phải ảnh hợp lệ.');
    $path = 'uploads/products/' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/' . $path)) throw new RuntimeException('Không lưu được ảnh. Kiểm tra quyền ghi uploads/products.');
    return $path;
}
