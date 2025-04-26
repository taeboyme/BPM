<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$error = '';
$success = '';

// Get current employee data
$stmt = $conn->prepare("SELECT password FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id); // changed to "s" since employee_id is varchar
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        if (!$employee || !password_verify($current_password, $employee['password'])) {
            throw new Exception("Current password is incorrect.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match.");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("New password must be at least 6 characters.");
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE employees SET password = ?, updated_at = NOW() WHERE employee_id = ?");
        $update->bind_param("ss", $hashed_password, $employee_id); // binding employee_id as varchar (s)

        if ($update->execute()) {
            $success = "Password changed successfully.";
        } else {
            throw new Exception("Failed to update password. Please try again.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - Attendance System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php';?>
<div class="container mt-5">
    <h2>Change Password</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-warning">Change Password</button>
        <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
