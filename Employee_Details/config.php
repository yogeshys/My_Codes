<?php
declare(strict_types=1);

$host = 'localhost';
$db   = 'employee_db';
$user = 'root';
$pass = ''; // Default XAMPP MySQL password is blank. Update if yours differs.
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Start Apache and MySQL in XAMPP, import database.sql in phpMyAdmin, then check config.php.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $type = ($f['type'] ?? '') === 'success' ? 'success' : 'error';
        echo '<div class="alert ' . $type . '">' . e($f['message'] ?? '') . '</div>';
        unset($_SESSION['flash']);
    }
}

/**
 * ID format: ECDI + YY + MM + four-digit global sequence.
 * Example: ECDI26100001 = joined October 2026, sequence 0001.
 * The database auto-increment key supplies the sequence so IDs remain unique
 * even when employees join in different months or years.
 */
function make_employee_code(string $joiningDate, int $sequence): string {
    $date = new DateTimeImmutable($joiningDate);
    return 'ECDI' . $date->format('ym') . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
}
