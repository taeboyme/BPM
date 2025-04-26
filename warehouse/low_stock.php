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

// Get threshold from settings or use default
$threshold = 10; // You can make this configurable through settings

// Get all low stock items
$low_stock_items = $inventory->getLowStockItems($threshold);

// Group items by warehouse
$items_by_warehouse = [];
foreach ($low_stock_items as $item) {
    $items_by_warehouse[$item['warehouse_id']][] = $item;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Alert - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .alert-card {
            border-left: 4px solid #dc3545;
        }
        .stock-warning {
            animation: blink 2s infinite;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Alert</h2>
            <div>
                <a href="inventory_list.php" class="btn btn-primary">
                    <i class="fas fa-boxes me-2"></i>View All Inventory
                </a>
            </div>
        </div>

        <?php if (empty($low_stock_items)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>All items are well stocked!
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle me-2"></i>
                Found <?php echo count($low_stock_items); ?> items with low stock (below <?php echo $threshold; ?> units)
            </div>

            <?php foreach ($items_by_warehouse as $warehouse_id => $items): 
                $warehouse_info = $warehouse->getWarehouseById($warehouse_id);
            ?>
                <div class="card mb-4 alert-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-warehouse me-2"></i>
                            <?php echo htmlspecialchars($warehouse_info['name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover low-stock-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Current Stock</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['description']); ?></small>
                                        </td>
                                        <td>
                                            <span class="<?php echo $item['quantity'] == 0 ? 'text-danger stock-warning' : 'text-warning'; ?>">
                                                <?php echo htmlspecialchars($item['quantity']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td>
                                            <?php if ($item['quantity'] == 0): ?>
                                                <span class="badge bg-danger stock-warning">Out of Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="stock_in.php?id=<?php echo $item['item_id']; ?>" 
                                                   class="btn btn-sm btn-success" title="Restock">
                                                    <i class="fas fa-plus-circle"></i>
                                                </a>
                                                <a href="inventory_edit.php?id=<?php echo $item['item_id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="movement_history.php?id=<?php echo $item['item_id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Movement History">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Export Options -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5><i class="fas fa-file-export me-2"></i>Export Report</h5>
                    <div class="btn-group">
                        <a href="export.php?type=low_stock&format=pdf" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                        <a href="export.php?type=low_stock&format=excel" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </a>
                        <a href="export.php?type=low_stock&format=csv" class="btn btn-primary">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.low-stock-table').DataTable({
                "order": [[1, "asc"]],
                "pageLength": 25,
                "language": {
                    "search": "Search items:"
                }
            });
        });
    </script>
</body>
</html>