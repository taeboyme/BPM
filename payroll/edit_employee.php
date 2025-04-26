<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$employee_id = $_GET['id'];

// Fetch employee details
$emp_query = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($emp_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    header("Location: index.php");
    exit();
}

// Fetch departments for dropdown
$dept_query = "SELECT department_id, department_name FROM departments";
$departments = $conn->query($dept_query);

// Fetch positions for dropdown
$pos_query = "SELECT position_id, position_title FROM positions";
$positions = $conn->query($pos_query);

// Fetch managers for dropdown
$manager_query = "SELECT employee_id, CONCAT(first_name, ' ', last_name) as manager_name 
                 FROM employees WHERE position_id
                 AND employee_id != ?";
$stmt = $conn->prepare($manager_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$managers = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare and execute the update query
    $query = "UPDATE employees SET 
        first_name = ?, last_name = ?, email = ?, phone = ?, 
        date_of_birth = ?, gender = ?, nationality = ?, marital_status = ?, 
        hire_date = ?, employment_status = ?, department_id = ?, position_id = ?, 
        manager_id = ?
        WHERE employee_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssssiiis", 
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['date_of_birth'],
        $_POST['gender'],
        $_POST['nationality'],
        $_POST['marital_status'],
        $_POST['hire_date'],
        $_POST['employment_status'],
        $_POST['department_id'],
        $_POST['position_id'],
        $_POST['manager_id'],
        $employee_id
    );

    if ($stmt->execute()) {
        header("Location: view_employees.php?success=1");
        exit();
    } else {
        $error = "Error updating employee: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Employee</h2>
            <a href="view_employees.php" class="btn btn-secondary">Back to List</a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" 
                               value="<?php echo $employee['first_name']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" 
                               value="<?php echo $employee['last_name']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo $employee['email']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo $employee['phone']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" 
                               value="<?php echo $employee['date_of_birth']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo $employee['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $employee['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $employee['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nationality" class="form-control" 
                               value="<?php echo $employee['nationality']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Marital Status</label>
                        <select name="marital_status" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="Single" <?php echo $employee['marital_status'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo $employee['marital_status'] == 'Married' ? 'selected' : ''; ?>>Married</option>
                            <option value="Divorced" <?php echo $employee['marital_status'] == 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                            <option value="Widowed" <?php echo $employee['marital_status'] == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Hire Date</label>
                        <input type="date" name="hire_date" class="form-control" 
                               value="<?php echo $employee['hire_date']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employment Status</label>
                        <select name="employment_status" class="form-control" required>
                            <option value="">Select Status</option>
                            <option value="ACTIVE" <?php echo $employee['employment_status'] == 'ACTIVE' ? 'selected' : ''; ?>>Active</option>
                            <option value="PROBATION" <?php echo $employee['employment_status'] == 'PROBATION' ? 'selected' : ''; ?>>Probation</option>
                            <option value="TERMINATED" <?php echo $employee['employment_status'] == 'TERMINATED' ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>"
                                        <?php echo $dept['department_id'] == $employee['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo $dept['department_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Position</label>
                        <select name="position_id" class="form-control" required>
                            <option value="">Select Position</option>
                            <?php while($pos = $positions->fetch_assoc()): ?>
                                <option value="<?php echo $pos['position_id']; ?>"
                                        <?php echo $pos['position_id'] == $employee['position_id'] ? 'selected' : ''; ?>>
                                    <?php echo $pos['position_title']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="view_employees.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
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