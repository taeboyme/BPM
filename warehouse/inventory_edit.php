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

$item_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$item_id) {
    header("Location: inventory_list.php");
    exit();
}

$item = $inventory->getItemById($item_id);
$warehouses = $warehouse->getWarehouses();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'item_name' => $_POST['item_name'],
        'description' => $_POST['description'],
        'unit' => $_POST['unit'],
        'warehouse_id' => $_POST['warehouse_id']
    ];

    // Validate inputs
    $errors = [];
    if (empty($data['item_name'])) {
        $errors[] = "Item name is required";
    }
    if (empty($data['unit'])) {
        $errors[] = "Unit is required";
    }

    // If no errors, proceed with update
    if (empty($errors)) {
        if ($inventory->updateItem($item_id, $data)) {
            header("Location: inventory_view.php?id=" . $item_id . "&success=1");
            exit();
        } else {
            $errors[] = "Failed to update item. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Inventory Item - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Inventory Item
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
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="item_name" 
                                       name="item_name" 
                                       value="<?php echo htmlspecialchars($item['item_name']); ?>"
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
                                          rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit of Measurement</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="unit" 
                                           name="unit" 
                                           value="<?php echo htmlspecialchars($item['unit']); ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a unit of measurement.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="warehouse_id" class="form-label">Warehouse</label>
                                <select class="form-select select2" id="warehouse_id" name="warehouse_id" required>
                                    <option value="">Select a warehouse</option>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?php echo $wh['warehouse_id']; ?>"
                                                <?php echo $item['warehouse_id'] == $wh['warehouse_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($wh['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a warehouse.
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Current Stock: <?php echo htmlspecialchars($item['quantity']); ?> <?php echo htmlspecialchars($item['unit']); ?>
                                <br>
                                <small>To adjust stock levels, use the Stock In/Out functions.</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="inventory_view.php?id=<?php echo $item_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to View
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Update Item
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
                                <?php echo $item['quantity'] > 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-trash-alt me-2"></i>Delete Item
                        </button>
                        <?php if ($item['quantity'] > 0): ?>
                            <small class="text-muted d-block mt-2">
                                Cannot delete item with existing stock.
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
                    <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="inventory_delete.php">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        <button type="submit" class="btn btn-danger">Delete Item</button>
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