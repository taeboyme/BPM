<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}
$employee_id = $_SESSION['employee_id'];
$error = '';
$success = '';

// Fetch employee data
$query = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile info
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $nationality = trim($_POST['nationality']);
        $marital_status = $_POST['marital_status'];

        try {
            $update = $conn->prepare("UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, gender=?, date_of_birth=?, nationality=?, marital_status=?, updated_at=NOW() WHERE employee_id=?");
            $update->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $gender, $date_of_birth, $nationality, $marital_status, $employee_id);
            if ($update->execute()) {
                $success = "Profile updated successfully.";
                $employee = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
                $employee->bind_param('s', $employee_id);
                $employee->execute();
                $employee = $employee->get_result()->fetch_assoc();
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
            if (!password_verify($current_password, $employee['password'])) {
                throw new Exception("Current password is incorrect.");
            }
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters.");
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE employees SET password=? WHERE employee_id=?");
            $update->bind_param("ss", $hashed_password, $employee_id);
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
    <title>My Profile - Project Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php';?>

<div class="container mt-5">
    <h2>My Profile</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Profile Update Form -->
    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Gender</label>
            <select name="gender" class="form-control">
                <option value="">-- Select --</option>
                <option value="Male" <?php if ($employee['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if ($employee['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                <option value="Other" <?php if ($employee['gender'] === 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Nationality</label>
            <input type="text" name="nationality" value="<?php echo htmlspecialchars($employee['nationality']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Marital Status</label>
            <select name="marital_status" class="form-control">
                <option value="">-- Select --</option>
                <option value="Single" <?php if ($employee['marital_status'] === 'Single') echo 'selected'; ?>>Single</option>
                <option value="Married" <?php if ($employee['marital_status'] === 'Married') echo 'selected'; ?>>Married</option>
                <option value="Other" <?php if ($employee['marital_status'] === 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Last Login</label>
            <input type="text" value="<?php echo $employee['last_login'] ? date('d M Y H:i', strtotime($employee['last_login'])) : 'Never'; ?>" class="form-control" readonly>
        </div>

        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<br/>
<br/>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html>
