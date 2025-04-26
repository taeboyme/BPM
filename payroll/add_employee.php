<?php 
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch departments
$dept_query = "SELECT department_id, department_name FROM departments";
$departments = $conn->query($dept_query);

// Fetch positions
$pos_query = "SELECT position_id, position_title FROM positions";
$positions = $conn->query($pos_query);

// Fetch managers (other employees)
$manager_query = "SELECT employee_id, CONCAT(first_name, ' ', last_name) as manager_name FROM employees";
$managers = $conn->query($manager_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match.";
    } else {
        // Generate unique employee ID
        $year = date('Y');
        $counter = 1;
        do {
            $employee_id = "EMP-$year-" . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $check_query = "SELECT COUNT(*) as count FROM employees WHERE employee_id = '$employee_id'";
            $check_result = $conn->query($check_query)->fetch_assoc();
            $exists = $check_result['count'] > 0;
            $counter++;
        } while ($exists);

        // Hash password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Handle optional manager_id
        $manager_id = !empty($_POST['manager_id']) ? $_POST['manager_id'] : null;

        // Prepare query
        $query = "INSERT INTO employees (
            employee_id, first_name, last_name, email, password, phone, 
            date_of_birth, gender, nationality, marital_status, 
            hire_date, employment_status, department_id, position_id, manager_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssssssiis",
            $employee_id,
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $hashed_password,
            $_POST['phone'],
            $_POST['date_of_birth'],
            $_POST['gender'],
            $_POST['nationality'],
            $_POST['marital_status'],
            $_POST['hire_date'],
            $_POST['employment_status'],
            $_POST['department_id'],
            $_POST['position_id'],
            $manager_id
        );

        if ($stmt->execute()) {
            header("Location: view_employees.php?success=1");
            exit();
        } else {
            $error = "Error adding employee: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Employee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Add New Employee</h2>
    <br/>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Nationality</label>
                <input type="text" name="nationality" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Marital Status</label>
                <select name="marital_status" class="form-control" required>
                    <option value="">Select Status</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                    <option value="Widowed">Widowed</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label>Hire Date</label>
                <input type="date" name="hire_date" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
                <label>Employment Status</label>
                <select name="employment_status" class="form-control" required>
                    <option value="">Select Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="PROBATION">Probation</option>
                    <option value="TERMINATED">Terminated</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 form-group">
                <label>Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">Select Department</option>
                    <?php while($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['department_id']; ?>">
                            <?php echo $dept['department_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>Position</label>
                <select name="position_id" class="form-control" required>
                    <option value="">Select Position</option>
                    <?php while($pos = $positions->fetch_assoc()): ?>
                        <option value="<?php echo $pos['position_id']; ?>">
                            <?php echo $pos['position_title']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>Manager (optional)</label>
                <select name="manager_id" class="form-control">
                    <option value="">Select Manager</option>
                    <?php while($manager = $managers->fetch_assoc()): ?>
                        <option value="<?php echo $manager['employee_id']; ?>">
                            <?php echo $manager['manager_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Add Employee</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    // Bootstrap validation
    (function () {
        'use strict';
        window.addEventListener('load', function () {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.forEach.call(forms, function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
</body>
</html>
