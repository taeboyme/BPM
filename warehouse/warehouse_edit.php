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

$warehouse_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$warehouse_id) {
    header("Location: warehouse_list.php");
    exit();
}

$warehouse_info = $warehouse->getWarehouseById($warehouse_id);
$employees = $employee->getAllEmployees();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
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
    if (!is_numeric($capacity) || $capacity < 0) {
        $errors[] = "Capacity must be a non-negative number";
    }

    // If no errors, proceed with the update
    if (empty($errors)) {
        if ($warehouse->updateWarehouse($warehouse_id, $name, $location, $capacity, $manager_id)) {
            header("Location: warehouse_view.php?id=" . $warehouse_id . "&success=1");
            exit();
        } else {
            $errors[] = "Failed to update warehouse. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Warehouse - Warehouse Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Warehouse
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
                                       value="<?php echo htmlspecialchars($warehouse_info['name']); ?>"
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
                                          required><?php echo htmlspecialchars($warehouse_info['location']); ?></textarea>
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
                                           min="0"
                                           value="<?php echo htmlspecialchars($warehouse_info['capacity']); ?>"
                                           required>
                                    <span class="input-group-text">units</span>
                                </div>
                                <div class="invalid-feedback">
                                    Please provide a valid capacity.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="manager_id" class="form-label">Warehouse Manager</label>
                                <select class="form-select select2" id="manager_id" name="manager_id">
                                    <option value="">Select a manager</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp['employee_id']; ?>"
                                                <?php echo $warehouse_info['manager_id'] == $emp['employee_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['first_name']); ?> 
                                            (<?php echo htmlspecialchars($emp['employee_id']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="warehouse_view.php?id=<?php echo $warehouse_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to View
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Update Warehouse
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card mt-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            These actions cannot be undone. Please be certain.
                        </p>
                        <button type="button" 
                                class="btn btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal"
                                <?php echo !empty($inventory_items) ? 'disabled' : ''; ?>>
                            <i class="fas fa-trash-alt me-2"></i>Delete Warehouse
                        </button>
                        <?php if (!empty($inventory_items)): ?>
                            <small class="text-muted d-block mt-2">
                                Cannot delete warehouse with existing inventory items.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this warehouse? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="warehouse_delete.php">
                        <input type="hidden" name="warehouse_id" value="<?php echo $warehouse_id; ?>">
                        <button type="submit" class="btn btn-danger">Delete Warehouse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });

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
