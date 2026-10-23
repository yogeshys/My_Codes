<?php
include 'db.php';

$error = ""; // Start with empty error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // 1. Required field validation
    if ($name === "" || $email === "" || $phone === "") {
        $error = "All fields are required.";
    } else {

        // 2. Check duplicate email
        $checkEmail = $conn->prepare("SELECT * FROM members_details WHERE Email_Id = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();

        if ($emailResult->num_rows > 0) {
            $error = "Email already exists!";
        }

        // 3. Check duplicate phone
        $checkPhone = $conn->prepare("SELECT * FROM members_details WHERE Mobile_Number = ?");
        $checkPhone->bind_param("s", $phone);
        $checkPhone->execute();
        $phoneResult = $checkPhone->get_result();

        if ($phoneResult->num_rows > 0) {
            $error = "Phone number already exists!";
        }

        // 4. Insert only if NO error
        if ($error === "") {
            $stmt = $conn->prepare("INSERT INTO members_details (Name, Email_Id, Mobile_Number) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $phone);
            $stmt->execute();
            $stmt->close();

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
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
<h2 class="text-center mb-4">Add New User</h2>

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
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>

        <button class="btn btn-success w-100">
            <i class="bi bi-check-circle"></i> Save User
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

