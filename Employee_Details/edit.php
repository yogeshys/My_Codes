<?php
require __DIR__ . '/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: employees.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM employees WHERE id = ?');
$stmt->execute([$id]);
$employee = $stmt->fetch();
if (!$employee) { flash('error', 'Employee record was not found.'); header('Location: employees.php'); exit; }

$errors = [];
$values = [
  'first_name' => $employee['first_name'],
  'last_name' => $employee['last_name'],
  'mobile' => $employee['mobile'],
  'designation' => $employee['designation'],
  'experience' => (string)$employee['experience'],
  'joining_date' => $employee['joining_date'],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  foreach ($values as $key => $_) $values[$key] = trim((string)($_POST[$key] ?? ''));
  if ($values['first_name'] === '') $errors[] = 'First name is required.';
  if ($values['last_name'] === '') $errors[] = 'Last name is required.';
  if ($values['mobile'] === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $values['mobile'])) $errors[] = 'Enter a valid mobile number.';
  if ($values['designation'] === '') $errors[] = 'Designation is required.';
  if ($values['experience'] === '' || !is_numeric($values['experience']) || (float)$values['experience'] < 0 || (float)$values['experience'] > 80) $errors[] = 'Enter a valid experience between 0 and 80 years.';
  $date = DateTime::createFromFormat('Y-m-d', $values['joining_date']);
  if (!$date || $date->format('Y-m-d') !== $values['joining_date']) $errors[] = 'Choose a valid joining date.';
  if (!$errors) {
    try {
      $pdo->beginTransaction();
      $newCode = make_employee_code($values['joining_date'], (int)$employee['id']);
      $update = $pdo->prepare('UPDATE employees SET employee_code = ?, first_name = ?, last_name = ?, mobile = ?, designation = ?, experience = ?, joining_date = ? WHERE id = ?');
      $update->execute([$newCode, $values['first_name'], $values['last_name'], $values['mobile'], $values['designation'], number_format((float)$values['experience'],2,'.',''), $values['joining_date'], $id]);
      $pdo->commit();
      flash('success', 'Employee details updated successfully.');
      header('Location: employees.php'); exit;
    } catch (PDOException $ex) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $errors[] = $ex->getCode() === '23000' ? 'This mobile number is already registered to another employee.' : 'Could not update the employee. Please try again.';
    }
  }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Employee | ECDI</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="directory-bg"><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">∞</span><span><strong>ESWAYS</strong><small>CONGLOMERATE DIGITAL INFINITY</small></span></a><nav><a class="nav-link" href="index.php">Register Employee</a><a class="nav-link active" href="employees.php">View Employees</a></nav></header>
<main class="edit-wrap"><div class="back-row"><a href="employees.php" class="back-link">← Back to employee directory</a></div><div class="edit-heading"><span class="eyebrow dark-eyebrow">EMPLOYEE RECORD</span><h1>Edit employee details</h1><p>Update the employee information below. Employee ID remains system-generated.</p></div>
<section class="form-section edit-card"><?php if ($errors): ?><div class="alert error"><strong>Please check the following:</strong><ul><?php foreach($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="employee-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-grid">
<div class="field full"><label>Employee ID</label><div class="input-with-icon"><span class="input-icon">⌁</span><input value="<?= e($employee['employee_code']) ?>" readonly><span class="auto-tag">SYSTEM</span></div><small class="field-help">The ID is recalculated if the joining date changes.</small></div>
<div class="field"><label for="first_name">First name <i>*</i></label><input id="first_name" name="first_name" required maxlength="80" value="<?= e($values['first_name']) ?>"></div>
<div class="field"><label for="last_name">Last name <i>*</i></label><input id="last_name" name="last_name" required maxlength="80" value="<?= e($values['last_name']) ?>"></div>
<div class="field"><label for="mobile">Mobile number <i>*</i></label><input id="mobile" name="mobile" type="tel" required maxlength="20" value="<?= e($values['mobile']) ?>"></div>
<div class="field"><label for="designation">Designation <i>*</i></label><input id="designation" name="designation" required maxlength="100" value="<?= e($values['designation']) ?>"></div>
<div class="field"><label for="experience">Experience (years) <i>*</i></label><div class="input-suffix"><input id="experience" name="experience" type="number" min="0" max="80" step="0.1" required value="<?= e($values['experience']) ?>"><span>years</span></div></div>
<div class="field"><label for="joining_date">Date of joining <i>*</i></label><div class="date-wrap"><input id="joining_date" name="joining_date" type="date" required value="<?= e($values['joining_date']) ?>"><span class="calendar-icon">▦</span></div></div>
</div><div class="form-footer"><p><span class="secure-icon">✓</span> Changes are saved to the employee database.</p><div class="button-row"><a class="btn btn-light" href="employees.php">Cancel</a><button class="btn btn-primary submit-btn" type="submit">Save changes <span>→</span></button></div></div></form></section><footer class="footer"><span>© <?= date('Y') ?> Esways Conglomerate Digital Infinity</span><span>Employee Portal <b>•</b> Internal use</span></footer></main></body></html>
