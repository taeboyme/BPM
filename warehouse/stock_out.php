<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Inventory.php';
require_once 'classes/Movement.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$inventory = new Inventory($conn);
$movement = new Movement($conn);

$item_id = isset($_GET['id']) ? $_GET['id'] : null;
$item = null;
if ($item_id) {
    $item = $inventory->getItemById($item_id);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dateInput = $_POST['date'] ?? '';
    $data = [
        'item_id' => $_POST['item_id'],
        'quantity' => $_POST['quantity'],
        'reference' => $_POST['reference'],
        'date' => $dateInput ? str_replace('T', ' ', $dateInput) . ':00' : date('Y-m-d H:i:s')
    ];

    $errors = [];
    if (empty($data['item_id'])) {
        $errors[] = "Item selection is required";
    }
    if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
        $errors[] = "Quantity must be a positive number";
    }

    if (empty($errors)) {
        $current_item = $inventory->getItemById($data['item_id']);
        if ($current_item['quantity'] < $data['quantity']) {
            $errors[] = "Insufficient stock. Available: " . $current_item['quantity'] . " " . $current_item['unit'];
        }
    }

    if (empty($errors)) {
        if ($inventory->updateStock($data['item_id'], $data['quantity'], 'OUT', $data['reference'], $data['date'])) {
            header("Location: inventory_list.php?success=1");
            exit();
        } else {
            $errors[] = "Failed to update stock. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Out - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-minus-circle me-2"></i>Stock Out Entry</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <?php if ($item): ?>
                                <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
                                <div class="alert alert-info">
                                    <strong>Selected Item:</strong> <?= htmlspecialchars($item['item_name']); ?><br>
                                    <strong>Available Stock:</strong> <?= htmlspecialchars($item['quantity']); ?> <?= htmlspecialchars($item['unit']); ?><br>
                                    <strong>Warehouse:</strong> <?= htmlspecialchars($item['warehouse_name']); ?>
                                </div>
                            <?php else: ?>
                                <label for="item_id" class="form-label">Select Item</label>
                                <select class="form-select select2" id="item_id" name="item_id" required>
                                    <option value="">Choose an item...</option>
                                    <?php 
                                    $items = $inventory->getAllInventoryItems();
                                    foreach ($items as $inv_item): 
                                        if ($inv_item['quantity'] > 0):
                                    ?>
                                    <option value="<?= $inv_item['item_id']; ?>"
                                            data-unit="<?= htmlspecialchars($inv_item['unit']); ?>"
                                            data-quantity="<?= htmlspecialchars($inv_item['quantity']); ?>"
                                            <?= (isset($_POST['item_id']) && $_POST['item_id'] == $inv_item['item_id']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($inv_item['item_name']); ?> 
                                        (Available: <?= htmlspecialchars($inv_item['quantity']); ?> <?= htmlspecialchars($inv_item['unit']); ?>)
                                    </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                                <div class="invalid-feedback">Please select an item.</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity to Remove</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity" 
                                       name="quantity" 
                                       min="1"
                                       max="<?= $item ? $item['quantity'] : ''; ?>"
                                       value="<?= isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>"
                                       required>
                                <span class="input-group-text" id="unit-display">
                                    <?= $item ? htmlspecialchars($item['unit']) : 'units'; ?>
                                </span>
                            </div>
                            <div class="invalid-feedback">Please enter a valid quantity.</div>
                            <small class="text-muted" id="stock-warning"></small>
                        </div>

                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference/Notes</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="reference" 
                                   name="reference" 
                                   value="<?= isset($_POST['reference']) ? htmlspecialchars($_POST['reference']) : ''; ?>" 
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">Movement Date</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   id="date" 
                                   name="date" 
                                   value="<?= isset($_POST['date']) ? htmlspecialchars($_POST['date']) : date('Y-m-d\TH:i'); ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="inventory_list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-minus-circle me-2"></i>Remove Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap-5' });

    $('#item_id').change(function() {
        let selectedOption = $(this).find('option:selected');
        let unit = selectedOption.data('unit');
        let quantity = selectedOption.data('quantity');

        $('#unit-display').text(unit || 'units');
        $('#quantity').attr('max', quantity);
        generateReference('out', selectedOption.val());
        updateStockWarning();
    });

    $('#quantity').on('input', updateStockWarning);

    function updateStockWarning() {
        let availableStock = $('#item_id option:selected').data('quantity');
        let requestedQuantity = $('#quantity').val();

        if (requestedQuantity > availableStock) {
            $('#stock-warning').html('<span class="text-danger">Warning: Requested quantity exceeds available stock!</span>');
        } else if (requestedQuantity > (availableStock * 0.8)) {
            $('#stock-warning').html('<span class="text-warning">Note: This will remove most of the available stock.</span>');
        } else {
            $('#stock-warning').html('');
        }
    }

    function generateReference(type, itemId) {
        const now = new Date();
        const timestamp = now.getFullYear().toString() +
            ('0' + (now.getMonth() + 1)).slice(-2) +
            ('0' + now.getDate()).slice(-2) + '-' +
            ('0' + now.getHours()).slice(-2) +
            ('0' + now.getMinutes()).slice(-2) +
            ('0' + now.getSeconds()).slice(-2);

        const reference = `MV-${timestamp}-${itemId}-${type.toUpperCase()}`;
        $('input[name="reference"]').val(reference);
    }

    // Auto-generate reference if item is preselected
    let selectedItem = $('#item_id').val();
    if (selectedItem) {
        generateReference('out', selectedItem);
    }
});

// Form validation
(function () {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
</body>
</html>
