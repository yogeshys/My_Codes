<?php
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM members_details WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: index.php");
exit;