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

// Handle item deletion
if (isset($_POST['delete_item']) && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    if ($inventory->deleteItem($item_id)) {
        $success_message = "Item deleted successfully!";
    } else {
        $error_message = "Failed to delete item. Check for existing movements.";
    }
}

// Get all warehouses for filtering
$warehouses = $warehouse->getWarehouses();

// Get inventory items (with optional warehouse filter)
$selected_warehouse = isset($_GET['warehouse_id']) ? $_GET['warehouse_id'] : null;
$inventory_items = $selected_warehouse ? 
    $inventory->getInventoryByWarehouse($selected_warehouse) : 
    $inventory->getAllInventoryItems();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory List - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Inventory Management</h2>
            <div>
                <a href="inventory_add.php" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Add New Item
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Warehouses</option>
                                <?php foreach ($warehouses as $wh): ?>
                                    <option value="<?php echo $wh['warehouse_id']; ?>"
                                            <?php echo $selected_warehouse == $wh['warehouse_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($wh['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>

                <table id="inventoryTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item Name</th>
                            <th>Warehouse</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory_items as $item): 
                            $warehouse_info = $warehouse->getWarehouseById($item['warehouse_id']);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($warehouse_info['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['unit']); ?></td>
                            <td>
                                <?php if ($item['quantity'] <= 0): ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif ($item['quantity'] < 10): ?>
                                    <span class="badge bg-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="inventory_view.php?id=<?php echo $item['item_id']; ?>" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="inventory_edit.php?id=<?php echo $item['item_id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $item['item_id']; ?>)"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this item?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="item_id" id="delete_item_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_item" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 25,
                "language": {
                    "search": "Search items:"
                }
            });
        });

        function confirmDelete(itemId) {
            document.getElementById('delete_item_id').value = itemId;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>