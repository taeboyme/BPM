<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Warehouse.php';
require_once 'classes/Inventory.php';
require_once 'classes/Employee.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$warehouse = new Warehouse($conn);
$inventory = new Inventory($conn);
$employee = new Employee($conn);

$warehouse_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$warehouse_id) {
    header("Location: warehouse_list.php");
    exit();
}

$warehouse_info = $warehouse->getWarehouseById($warehouse_id);
$inventory_items = $inventory->getInventoryByWarehouse($warehouse_id);
$manager = $employee->getEmployeeById($warehouse_info['manager_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Details - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-4">
                <!-- Warehouse Information Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-warehouse me-2"></i><?php echo htmlspecialchars($warehouse_info['name']); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Location:</strong><br>
                            <?php echo nl2br(htmlspecialchars($warehouse_info['location'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Manager:</strong><br>
                            <?php if ($manager): ?>
                                <?php echo htmlspecialchars($manager['first_name'].' '.$manager['last_name']); ?><br>
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($manager['email']); ?><br>
                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($manager['phone']); ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">No manager assigned</span>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Capacity:</strong><br>
                            <?php 
                            $total_items = array_sum(array_column($inventory_items, 'quantity'));
                            $capacity_percentage = ($warehouse_info['capacity'] > 0) ? 
                                                 ($total_items / $warehouse_info['capacity']) * 100 : 0;
                            ?>
                            <div class="progress mb-2">
                                <div class="progress-bar <?php echo $capacity_percentage > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $capacity_percentage; ?>%">
                                    <?php echo round($capacity_percentage, 1); ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php echo $total_items; ?> of <?php echo $warehouse_info['capacity']; ?> units used
                            </small>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong><br>
                                <?php if ($capacity_percentage > 90): ?>
                                    <span class="badge bg-danger">Full</span>
                                <?php elseif ($capacity_percentage > 70): ?>
                                    <span class="badge bg-warning">High</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Available</span>
                                <?php endif; ?>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="warehouse_edit.php?id=<?php echo $warehouse_id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Warehouse
                            </a>
                            <a href="stock_in.php?warehouse=<?php echo $warehouse_id; ?>" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>Add Stock
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h3><?php echo count($inventory_items); ?></h3>
                                <small class="text-muted">Total Items</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h3><?php echo count(array_filter($inventory_items, function($item) { return $item['quantity'] <= 10; })); ?></h3>
                                <small class="text-muted">Low Stock Items</small>
                            </div>
                            <div class="col-6">
                                <h3><?php echo count(array_filter($inventory_items, function($item) { return $item['quantity'] == 0; })); ?></h3>
                                <small class="text-muted">Out of Stock</small>
                            </div>
                            <div class="col-6">
                                <h3><?php echo count(array_filter($inventory_items, function($item) { return $item['quantity'] > 0; })); ?></h3>
                                <small class="text-muted">Available Items</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Inventory Items Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Inventory Items</h5>
                    </div>
                    <div class="card-body">
                        <table id="inventoryTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td>
                                            <?php if ($item['quantity'] <= 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($item['quantity'] <= 10): ?>
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
                                                <a href="stock_in.php?id=<?php echo $item['item_id']; ?>" 
                                                   class="btn btn-sm btn-success" title="Stock In">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                <a href="stock_out.php?id=<?php echo $item['item_id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Stock Out">
                                                    <i class="fas fa-minus"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                "pageLength": 25,
                "order": [[0, "asc"]],
                "language": {
                    "search": "Search items:"
                }
            });
        });
    </script>
</body>
</html>
