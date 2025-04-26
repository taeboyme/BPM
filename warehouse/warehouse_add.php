<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Warehouse.php';
require_once 'classes/Employee.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$warehouse = new Warehouse($conn);
$employee = new Employee($conn);

$managers = $employee->getManagers(); // Get all employees who can be managers

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];
    $manager_id = $_POST['manager_id'];

    // Validate inputs
    $errors = [];
    if (empty($name)) {
        $errors[] = "Warehouse name is required";
    }
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    if (!is_numeric($capacity) || $capacity <= 0) {
        $errors[] = "Capacity must be a positive number";
    }
    if (empty($manager_id)) {
        $errors[] = "Manager selection is required";
    }

    // If no errors, proceed with warehouse creation
    if (empty($errors)) {
        if ($warehouse->addWarehouse($name, $location, $capacity, $manager_id)) {
            header("Location: warehouse_list.php?success=1");
            exit();
        } else {
            $errors[] = "Failed to add warehouse. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Warehouse - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Add New Warehouse
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Warehouse Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please provide a warehouse name.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <textarea class="form-control" 
                                          id="location" 
                                          name="location" 
                                          rows="3" 
                                          required><?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?></textarea>
                                <div class="invalid-feedback">
                                    Please provide a location.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="capacity" class="form-label">Storage Capacity</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="capacity" 
                                           name="capacity" 
                                           min="1"
                                           value="<?php echo isset($_POST['capacity']) ? htmlspecialchars($_POST['capacity']) : ''; ?>"
                                           required>
                                    <span class="input-group-text">units</span>
                                    <div class="invalid-feedback">
                                        Please provide a valid capacity.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="manager_id" class="form-label">Warehouse Manager</label>
                                <select class="form-select" id="manager_id" name="manager_id" required>
                                    <option value="">Select a manager</option>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?php echo htmlspecialchars($manager['employee_id']); ?>"
                                                <?php echo (isset($_POST['manager_id']) && $_POST['manager_id'] == $manager['employee_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($manager['first_name'] .' '. $manager['last_name']); ?> 
                                            (ID: <?php echo htmlspecialchars($manager['employee_id']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a manager.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="warehouse_list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Warehouse
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>