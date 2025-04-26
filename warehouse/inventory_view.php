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

if (!$item_id) {
    header("Location: inventory_list.php");
    exit();
}

$item = $inventory->getItemById($item_id);
$recent_movements = $movement->getRecentMovements($item_id, 10);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Item Details - Warehouse Management System</title>
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
                <!-- Item Information Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-box me-2"></i><?php echo htmlspecialchars($item['item_name']); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Description:</strong><br>
                            <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Current Stock:</strong><br>
                            <h3><?php echo htmlspecialchars($item['quantity']); ?> 
                                <small class="text-muted"><?php echo htmlspecialchars($item['unit']); ?></small>
                            </h3>
                            <?php if ($item['quantity'] <= 10): ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Warehouse:</strong><br>
                            <?php echo htmlspecialchars($item['warehouse_name']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong><br>
                            <?php if ($item['quantity'] <= 0): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php elseif ($item['quantity'] <= 10): ?>
                                <span class="badge bg-warning">Low Stock</span>
                            <?php else: ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="stock_in.php?id=<?php echo $item_id; ?>" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>Stock In
                            </a>
                            <a href="stock_out.php?id=<?php echo $item_id; ?>" class="btn btn-danger">
                                <i class="fas fa-minus-circle me-2"></i>Stock Out
                            </a>
                            <a href="inventory_edit.php?id=<?php echo $item_id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Item
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Movement Statistics</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $monthly_stats = $movement->getMonthlyStatistics($item_id);
                        ?>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h3 class="text-success"><?php echo $monthly_stats['total_in']; ?></h3>
                                <small class="text-muted">Stock In (This Month)</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h3 class="text-danger"><?php echo $monthly_stats['total_out']; ?></h3>
                                <small class="text-muted">Stock Out (This Month)</small>
                            </div>
                            <div class="col-6">
                                <h3><?php echo $monthly_stats['avg_quantity']; ?></h3>
                                <small class="text-muted">Average Stock</small>
                            </div>
                            <div class="col-6">
                                <h3><?php echo $monthly_stats['movement_count']; ?></h3>
                                <small class="text-muted">Total Movements</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Recent Movements Table -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Movements</h5>
                            <a href="movement_history.php?id=<?php echo $item_id; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-history me-2"></i>View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="movementTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_movements as $movement): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($movement['movement_date'])); ?></td>
                                        <td>
                                            <?php if ($movement['movement_type'] == 'IN'): ?>
                                                <span class="badge bg-success">Stock In</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Stock Out</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($movement['movement_type'] == 'IN'): ?>
                                                <span class="text-success">+<?php echo htmlspecialchars($movement['quantity']); ?></span>
                                            <?php else: ?>
                                                <span class="text-danger">-<?php echo htmlspecialchars($movement['quantity']); ?></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($item['unit']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($movement['reference']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Stock Level Chart -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Stock Level Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="stockTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#movementTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "searching": false,
                "lengthChange": false
            });

            // Stock Trend Chart
            const ctx = document.getElementById('stockTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: <?php echo json_encode($movement->getStockTrendData($item_id)); ?>,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>