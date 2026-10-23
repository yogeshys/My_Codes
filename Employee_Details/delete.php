<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: employees.php'); exit; }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { flash('error', 'Invalid employee selected.'); header('Location: employees.php'); exit; }
$stmt = $pdo->prepare('SELECT employee_code, first_name, last_name FROM employees WHERE id = ?');
$stmt->execute([$id]);
$employee = $stmt->fetch();
if (!$employee) {
    flash('error', 'Employee record was not found.');
} else {
    $delete = $pdo->prepare('DELETE FROM employees WHERE id = ?');
    $delete->execute([$id]);
    flash('success', 'Deleted employee ' . $employee['employee_code'] . ' (' . $employee['first_name'] . ' ' . $employee['last_name'] . ').');
}
header('Location: employees.php');
exit;
