<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Warehouse.php';
require_once 'classes/Inventory.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$warehouse = new Warehouse($conn);
$inventory = new Inventory($conn);

// Handle warehouse deletion if requested
if (isset($_POST['delete_warehouse']) && isset($_POST['warehouse_id'])) {
    $warehouse_id = $_POST['warehouse_id'];
    // Check if warehouse has inventory
    $items = $inventory->getInventoryByWarehouse($warehouse_id);
    if (empty($items)) {
        $warehouse->deleteWarehouse($warehouse_id);
        $success_message = "Warehouse deleted successfully!";
    } else {
        $error_message = "Cannot delete warehouse with existing inventory!";
    }
}

// Get all warehouses
$warehouses = $warehouse->getWarehouses();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warehouse List - Warehouse Management System</title>
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
            <h2>Warehouse Management</h2>
            <a href="warehouse_add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Warehouse
            </a>
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
                <table id="warehouseTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Manager</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouses as $wh): 
                            $items = $inventory->getInventoryByWarehouse($wh['warehouse_id']);
                            $total_items = 0;
                            foreach ($items as $item) {
                                $total_items += $item['quantity'];
                            }
                            $usage_percentage = ($wh['capacity'] > 0) ? ($total_items / $wh['capacity']) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($wh['warehouse_id']); ?></td>
                            <td><?php echo htmlspecialchars($wh['name']); ?></td>
                            <td><?php echo htmlspecialchars($wh['location']); ?></td>
                            <td><?php echo htmlspecialchars($wh['capacity']); ?></td>
                            <td><?php echo htmlspecialchars($wh['manager_name']); ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar <?php echo $usage_percentage > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $usage_percentage; ?>%">
                                        <?php echo round($usage_percentage, 1); ?>%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($usage_percentage > 90): ?>
                                    <span class="badge bg-danger">Full</span>
                                <?php elseif ($usage_percentage > 70): ?>
                                    <span class="badge bg-warning">High</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="warehouse_view.php?id=<?php echo $wh['warehouse_id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="warehouse_edit.php?id=<?php echo $wh['warehouse_id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $wh['warehouse_id']; ?>)">
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
                    Are you sure you want to delete this warehouse?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="warehouse_id" id="delete_warehouse_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_warehouse" class="btn btn-danger">Delete</button>
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
            $('#warehouseTable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 10,
                "language": {
                    "search": "Search warehouses:"
                }
            });
        });

        function confirmDelete(warehouseId) {
            document.getElementById('delete_warehouse_id').value = warehouseId;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>