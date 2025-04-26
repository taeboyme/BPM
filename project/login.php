<?php
require_once 'config/database.php';
session_start();

if (isset($_SESSION['employee_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    $query = "SELECT * FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];

            // Update last login
            $update = "UPDATE employees SET last_login = NOW() WHERE employee_id = ?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param('s', $user['employee_id']);
            $stmt->execute();

            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Employee not found';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Login - Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
            height: auto;
        }
        .btn-login {
            padding: 10px;
            font-weight: bold;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="card">
        <div class="card-header">
            <br/>
            <h4>Project Management System</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Employee ID</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-id-badge"></i>
                            </span>
                        </div>
                        <input type="text" name="employee_id" class="form-control" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                        <label class="custom-control-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-login">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </div>
    </div>

    <div class="text-center mt-3 text-muted">
        <small>&copy; <?php echo date('Y'); ?> Project Management System. All rights reserved.</small>
    </div>
    <div class="text-center mt-3">
            <a href="../index.php">Back to Homepage</a>
        </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://kit.fontawesome.com/your-kit-code.js"></script>
<script>
$(document).ready(function() {
    $('form')[0].reset();
    $('.input-group-text').click(function() {
        var input = $(this).closest('.input-group').find('input');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-lock').addClass('fa-unlock');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-unlock').addClass('fa-lock');
        }
    });
});
</script>
</body>
</html>
