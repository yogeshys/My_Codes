<?php
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$error = "";

// Fetch existing user
$stmt = $conn->prepare("SELECT * FROM members_details WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Duplicate email check
    $checkEmail = $conn->prepare("SELECT * FROM members_details WHERE Email_Id = ? AND ID != ?");
    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    if ($checkEmail->get_result()->num_rows > 0) {
        $error = "Email already exists!";
    }

    // Duplicate phone check
    $checkPhone = $conn->prepare("SELECT * FROM members_details WHERE Mobile_Number = ? AND ID != ?");
    $checkPhone->bind_param("si", $phone, $id);
    $checkPhone->execute();
    if ($checkPhone->get_result()->num_rows > 0) {
        $error = "Phone number already exists!";
    }

    // Update only if no errors
    if ($error === "") {
        $stmt = $conn->prepare("UPDATE members_details SET Name = ?, Email_Id = ?, Mobile_Number = ? WHERE ID = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">User Manager</a>
  </div>
</nav>

<div class="container mt-4">
   <h2 class="text-center mb-4">Edit User</h2>
<?php if(!empty($error)): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-circle"></i> <?= $error ?>
</div>
<?php endif; ?>
<a href="index.php" class="btn btn-secondary mb-3">
    <i class="bi bi-arrow-left"></i> Back
</a>

<div class="card shadow p-4">
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= $user['Name'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= $user['Email_Id'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= $user['Mobile_Number'] ?>" required>
        </div>

        <button class="btn btn-warning w-100">
            <i class="bi bi-pencil-square"></i> Update User
        </button>
    </form>
</div>
</div>

<!-- FOOTER -->
<footer class="bg-primary text-white text-center py-3 mt-5">
    <p class="mb-0">Test Site © 2026 | Built by Yogesh</p>
</footer>

</body>
</html>

