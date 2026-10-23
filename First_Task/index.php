<?php
include 'db.php';

// Reset AUTO_INCREMENT automatically if table is empty
$result = $conn->query("SELECT COUNT(*) AS total FROM Members_Details");
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    $conn->query("ALTER TABLE Members_Details AUTO_INCREMENT = 1");
}

// Fetch all users
$result = $conn->query("SELECT * FROM members_details ORDER BY id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users CRUD</title>
    <!-- Bootstrap CSS -->
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
    <a class="navbar-brand fw-bold" href="index.php">My First Task</a>
  </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center">User List</h1>

<div class="d-flex justify-content-end mb-3">
    <a href="create.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Add User
    </a>
</div>

<div class="card shadow">
    <div class="card-body">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['ID'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <td><?= $row['Email_Id'] ?></td>
                    <td><?= $row['Mobile_Number'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['ID'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>

                        <!-- Delete Button triggers Modal -->
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['ID'] ?>">
                            <i class="bi bi-trash"></i> Delete
                        </button>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?= $row['ID'] ?>">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Confirm Delete</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <div class="modal-body">
                                Are you sure you want to delete <strong><?= $row['Name'] ?></strong>?
                              </div>
                              <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <a href="delete.php?id=<?= $row['ID'] ?>" class="btn btn-danger">Delete</a>
                              </div>
                            </div>
                          </div>
                        </div>

                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No users found
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- FOOTER -->
<footer class="bg-primary text-white text-center py-3 mt-5">
    <p class="mb-0">Test Site © 2026 | Built by Yogesh</p>
</footer>

</body>
</html>
