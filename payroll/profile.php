<?php
session_start();
require_once 'config/database.php';
include 'includes/navbar.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$error = '';
$success = '';

// Fetch user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile info
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        try {
            if ($username !== $user['username']) {
                $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
                $check->bind_param('si', $username, $user_id);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    throw new Exception("Username already exists");
                }
            }

            $update = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ? WHERE user_id = ?");
            $update->bind_param("ssssi", $username, $email, $first_name, $last_name, $user_id);
            if ($update->execute()) {
                $success = "Profile updated successfully.";
                $_SESSION['username'] = $username;
                $stmt->execute(); // Refresh user data
                $user = $stmt->get_result()->fetch_assoc();
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        try {
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect.");
            }
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters.");
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update->bind_param("si", $hashed_password, $user_id);
            if ($update->execute()) {
                $success = "Password changed successfully.";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>My Profile</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo $user['username']; ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <input type="text" value="<?php echo ucfirst($user['role']); ?>" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>Status</label>
            <input type="text" value="<?php echo ucfirst($user['status']); ?>" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>Last Login</label>
            <input type="text" value="<?php echo $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never'; ?>" class="form-control" readonly>
        </div>

        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
    </form>
</div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html>
