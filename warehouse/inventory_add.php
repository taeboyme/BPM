<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Inventory.php';
require_once 'classes/Warehouse.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$inventory = new Inventory($conn);
$warehouse = new Warehouse($conn);

// Get all warehouses for selection
$warehouses = $warehouse->getWarehouses();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'warehouse_id' => $_POST['warehouse_id'],
        'item_name' => $_POST['item_name'],
        'description' => $_POST['description'],
        'quantity' => $_POST['quantity'],
        'unit' => $_POST['unit']
    ];

    // Validate inputs
    $errors = [];
    if (empty($data['warehouse_id'])) {
        $errors[] = "Warehouse selection is required";
    }
    if (empty($data['item_name'])) {
        $errors[] = "Item name is required";
    }
    if (!is_numeric($data['quantity']) || $data['quantity'] < 0) {
        $errors[] = "Quantity must be a non-negative number";
    }
    if (empty($data['unit'])) {
        $errors[] = "Unit is required";
    }

    // If no errors, proceed with item creation
    if (empty($errors)) {
        if ($inventory->addItem($data['warehouse_id'], $data['item_name'], 
                              $data['description'], $data['quantity'], $data['unit'])) {
            header("Location: inventory_list.php?success=1");
            exit();
        } else {
            $errors[] = "Failed to add item. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Item - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
                            <i class="fas fa-plus-circle me-2"></i>Add New Inventory Item
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
                                <label for="warehouse_id" class="form-label">Warehouse</label>
                                <select class="form-select select2" id="warehouse_id" name="warehouse_id" required>
                                    <option value="">Select a warehouse</option>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?php echo $wh['warehouse_id']; ?>"
                                                <?php echo (isset($_POST['warehouse_id']) && $_POST['warehouse_id'] == $wh['warehouse_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($wh['name']); ?> 
                                            (<?php echo htmlspecialchars($wh['location']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a warehouse.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="item_name" 
                                       name="item_name" 
                                       value="<?php echo isset($_POST['item_name']) ? htmlspecialchars($_POST['item_name']) : ''; ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Please provide an item name.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">Initial Quantity</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="quantity" 
                                           name="quantity" 
                                           min="0"
                                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '0'; ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a valid quantity.
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="unit" class="form-label">Unit</label>
                                    <select class="form-select" id="unit" name="unit" required>
                                        <option value="">Select unit</option>
                                        <option value="pieces" <?php echo (isset($_POST['unit']) && $_POST['unit'] == 'pieces') ? 'selected' : ''; ?>>Pieces</option>
                                        <option value="boxes" <?php echo (isset($_POST['unit']) && $_POST['unit'] == 'boxes') ? 'selected' : ''; ?>>Boxes</option>
                                        <option value="kg" <?php echo (isset($_POST['unit']) && $_POST['unit'] == 'kg') ? 'selected' : ''; ?>>Kilograms</option>
                                        <option value="liters" <?php echo (isset($_POST['unit']) && $_POST['unit'] == 'liters') ? 'selected' : ''; ?>>Liters</option>
                                        <option value="meters" <?php echo (isset($_POST['unit']) && $_POST['unit'] == 'meters') ? 'selected' : ''; ?>>Meters</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a unit.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="inventory_list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Item
                                </button>
                            </div>
                        </form>
                    </div>
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