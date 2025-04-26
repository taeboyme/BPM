<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if employee exists
        $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'No account found with that email.';
        } else {
            $employee = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Save token and expiry in DB
            $update = $conn->prepare("UPDATE employees SET password_reset_token = ?, password_reset_expires = ? WHERE employee_id = ?");
            $update->bind_param("sss", $token, $expires, $employee['employee_id']);
            $update->execute();

            // Simulate sending email (in real app, send via mail())
            $reset_link = "http://yourdomain.com/reset_password.php?token=$token";

            // Show the link on-screen for testing/demo purposes
            $success = "A password reset link has been generated. <br><a href='" . htmlspecialchars($reset_link) . "'>$reset_link</a>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5" style="max-width: 500px;">
        <h4 class="mb-4 text-center">Forgot Password</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Enter your email address</label>
                <input type="email" name="email" class="form-control" required placeholder="Email address">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
