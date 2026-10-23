<?php
require __DIR__ . '/config.php';
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE employee_code LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR mobile LIKE ? OR designation LIKE ? ORDER BY id DESC');
    $stmt->execute([$like, $like, $like, $like, $like]);
    $employees = $stmt->fetchAll();
} else {
    $employees = $pdo->query('SELECT * FROM employees ORDER BY id DESC')->fetchAll();
}
$total = (int)$pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Employee Directory | ECDI</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="directory-bg">
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">∞</span><span><strong>ESWAYS</strong><small>CONGLOMERATE DIGITAL INFINITY</small></span></a><nav><a class="nav-link" href="index.php">Register Employee</a><a class="nav-link active" href="employees.php">View Employees <span class="nav-count"><?= $total ?></span></a></nav></header>
<main class="directory-wrap">
  <div class="directory-hero"><div><span class="eyebrow"><span class="pulse"></span> PEOPLE DIRECTORY</span><h1>Employee <span>directory.</span></h1><p>Browse employee records and manage details securely.</p></div><a class="btn btn-primary" href="index.php#employee-form">＋ Add employee</a></div>
  <?php show_flash(); ?>
  <section class="directory-card">
    <div class="directory-toolbar"><div><h2>All employees <span class="total-pill"><?= count($employees) ?></span></h2><p><?= $q !== '' ? 'Search results for “' . e($q) . '”' : 'Your company workforce at a glance' ?></p></div><form class="search-form" method="get"><span>⌕</span><input name="q" value="<?= e($q) ?>" placeholder="Search name, ID, mobile..."><button type="submit">Search</button><?php if ($q !== ''): ?><a href="employees.php" class="clear-search">Clear</a><?php endif; ?></form></div>
    <div class="table-scroll"><table><thead><tr><th>Employee</th><th>Employee ID</th><th>Mobile number</th><th>Designation</th><th>Experience</th><th>Date joined</th><th>Actions</th></tr></thead><tbody>
    <?php if (!$employees): ?><tr><td colspan="7"><div class="empty-state"><span>◎</span><strong>No employees found</strong><p><?= $q !== '' ? 'Try another search term.' : 'Register your first employee to get started.' ?></p><a href="index.php#employee-form" class="btn btn-primary">Register employee</a></div></td></tr>
    <?php else: foreach ($employees as $emp): ?><tr><td><div class="person-cell"><span class="avatar"><?= e(strtoupper(substr($emp['first_name'],0,1) . substr($emp['last_name'],0,1))) ?></span><span><strong><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></strong><small>Employee</small></span></div></td><td><span class="employee-id"><?= e($emp['employee_code']) ?></span></td><td><?= e($emp['mobile']) ?></td><td><?= e($emp['designation']) ?></td><td><?= e(rtrim(rtrim(number_format((float)$emp['experience'], 1), '0'), '.')) ?> yrs</td><td><?= e(date('d M Y', strtotime($emp['joining_date']))) ?></td><td><div class="action-group"><a class="icon-btn edit-action" href="edit.php?id=<?= (int)$emp['id'] ?>" title="Edit employee" aria-label="Edit employee">✎</a><form method="post" action="delete.php" class="delete-form" onsubmit="return confirm('Delete <?= e($emp['first_name'] . ' ' . $emp['last_name']) ?> (<?= e($emp['employee_code']) ?>)? This action cannot be undone.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$emp['id'] ?>"><button class="icon-btn delete-action" type="submit" title="Delete employee" aria-label="Delete employee">⌫</button></form></div></td></tr><?php endforeach; endif; ?>
    </tbody></table></div>
    <div class="table-bottom"><span>Showing <strong><?= count($employees) ?></strong> record(s)<?= $q !== '' ? ' matching your search' : '' ?></span><span><i class="status-dot"></i> Employee records</span></div>
  </section>
  <footer class="footer"><span>© <?= date('Y') ?> Esways Conglomerate Digital Infinity</span><span>Employee Portal <b>•</b> Internal use</span></footer>
</main>
</body></html>
