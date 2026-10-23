<?php
require __DIR__ . '/config.php';

$errors = [];
$values = [
    'first_name' => '',
    'last_name' => '',
    'mobile' => '',
    'designation' => '',
    'experience' => '',
    'joining_date' => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_) {
        $values[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if ($values['first_name'] === '') $errors[] = 'First name is required.';
    if ($values['last_name'] === '') $errors[] = 'Last name is required.';
    if ($values['mobile'] === '') $errors[] = 'Mobile number is required.';
    if ($values['designation'] === '') $errors[] = 'Designation is required.';
    if ($values['experience'] === '' || !is_numeric($values['experience']) || (float)$values['experience'] < 0 || (float)$values['experience'] > 80) {
        $errors[] = 'Enter a valid experience between 0 and 80 years.';
    }
    $date = DateTime::createFromFormat('Y-m-d', $values['joining_date']);
    if (!$date || $date->format('Y-m-d') !== $values['joining_date']) $errors[] = 'Choose a valid joining date.';
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $values['mobile'])) $errors[] = 'Enter a valid mobile number (7–20 characters, digits and common symbols only).';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            // Reserve a unique database sequence, then construct the readable employee code.
            $stmt = $pdo->prepare('INSERT INTO employees (employee_code, first_name, last_name, mobile, designation, experience, joining_date) VALUES (?, ?, ?, ?, ?, ?, ?)');
            // Temporary unique placeholder is replaced after INSERT using the generated numeric key.
            $stmt->execute([
                'PENDING-' . bin2hex(random_bytes(8)),
                $values['first_name'],
                $values['last_name'],
                $values['mobile'],
                $values['designation'],
                number_format((float)$values['experience'], 2, '.', ''),
                $values['joining_date'],
            ]);
            $id = (int)$pdo->lastInsertId();
            $code = make_employee_code($values['joining_date'], $id);
            $update = $pdo->prepare('UPDATE employees SET employee_code = ? WHERE id = ?');
            $update->execute([$code, $id]);
            $pdo->commit();

            flash('success', 'Employee registered successfully. Employee ID: ' . $code);
            header('Location: index.php');
            exit;
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($ex->getCode() === '23000') {
                $errors[] = 'This mobile number is already registered. Duplicate employee mobile numbers are not allowed.';
            } else {
                $errors[] = 'Could not save employee details. Please check the database setup and try again.';
            }
        }
    }
}
$total = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ECDI | Employee Portal</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="portal-bg">
<header class="topbar">
  <a class="brand" href="index.php" aria-label="ECDI Home">
    <span class="brand-mark">∞</span>
    <span><strong>ESWAYS</strong><small>CONGLOMERATE DIGITAL INFINITY</small></span>
  </a>
  <nav><a class="nav-link active" href="index.php">Register Employee</a><a class="nav-link" href="employees.php">View Employees <span class="nav-count"><?= $total ?></span></a></nav>
</header>

<main class="page-wrap">
  <section class="hero">
    <div class="hero-copy">
      <span class="eyebrow"><span class="pulse"></span> PEOPLE • PROCESS • PROGRESS</span>
      <h1>Every great company<br><span>starts with its people.</span></h1>
      <p>Welcome to the Esways Conglomerate Digital Infinity employee portal. Create a new employee profile and keep your workforce details organised in one place.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="#employee-form">Register an employee <span>↘</span></a>
        <a class="btn btn-ghost" href="employees.php">View employees <span>→</span></a>
      </div>
      <div class="hero-metrics"><div><strong><?= $total ?></strong><span>Registered employees</span></div><div><strong>ECDI</strong><span>Unique employee identity</span></div></div>
    </div>
    <div class="hero-art" aria-hidden="true">
      <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
      <div class="infinity-symbol">∞</div>
      <div class="art-caption"><span class="art-dot"></span><div><strong>Connected by people</strong><small>Building tomorrow, together</small></div></div>
    </div>
  </section>

  <section class="form-section" id="employee-form">
    <div class="section-heading"><div><span class="eyebrow dark-eyebrow">EMPLOYEE ONBOARDING</span><h2>Employee details</h2><p>Enter the details below to create an employee record.</p></div><span class="required-note"><i>*</i> Required fields</span></div>
    <?php show_flash(); ?>
    <?php if ($errors): ?><div class="alert error"><strong>Please check the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="index.php#employee-form" class="employee-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="form-grid">
        <div class="field full"><label for="employee_code_preview">Employee ID</label><div class="input-with-icon"><span class="input-icon">⌁</span><input id="employee_code_preview" type="text" value="Generated automatically after saving" readonly><span class="auto-tag">AUTO</span></div><small class="field-help">Format: ECDI + joining year + month + sequence. Example: ECDI26100001.</small></div>
        <div class="field"><label for="first_name">First name <i>*</i></label><input id="first_name" name="first_name" type="text" maxlength="80" placeholder="Enter first name" value="<?= e($values['first_name']) ?>" required autocomplete="given-name"></div>
        <div class="field"><label for="last_name">Last name <i>*</i></label><input id="last_name" name="last_name" type="text" maxlength="80" placeholder="Enter last name" value="<?= e($values['last_name']) ?>" required autocomplete="family-name"></div>
        <div class="field"><label for="mobile">Mobile number <i>*</i></label><input id="mobile" name="mobile" type="tel" maxlength="20" placeholder="e.g. 98765 43210" value="<?= e($values['mobile']) ?>" required autocomplete="tel"><small class="field-help">Must be unique for every employee.</small></div>
        <div class="field"><label for="designation">Designation <i>*</i></label><input id="designation" name="designation" type="text" maxlength="100" placeholder="e.g. Software Engineer" value="<?= e($values['designation']) ?>" required></div>
        <div class="field"><label for="experience">Experience (years) <i>*</i></label><div class="input-suffix"><input id="experience" name="experience" type="number" min="0" max="80" step="0.1" placeholder="e.g. 2.5" value="<?= e($values['experience']) ?>" required><span>years</span></div></div>
        <div class="field"><label for="joining_date">Date of joining <i>*</i></label><div class="date-wrap"><input id="joining_date" name="joining_date" type="date" value="<?= e($values['joining_date']) ?>" required><span class="calendar-icon">▦</span></div><small class="field-help">Employee ID month and year follow this date.</small></div>
      </div>
      <div class="form-footer"><p><span class="secure-icon">✓</span> Employee ID is generated by the system and cannot be entered manually.</p><button class="btn btn-primary submit-btn" type="submit">Save employee <span>→</span></button></div>
    </form>
  </section>
  <footer class="footer"><span>© <?= date('Y') ?> Esways Conglomerate Digital Infinity</span><span>Employee Portal <b>•</b> Internal use</span></footer>
</main>
</body>
</html>
