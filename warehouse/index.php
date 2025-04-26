<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Warehouse.php';
require_once 'classes/Inventory.php';
require_once 'classes/Movement.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$warehouse = new Warehouse($conn);
$inventory = new Inventory($conn);
$movement = new Movement($conn);

// Get summary data
$warehouses = $warehouse->getWarehouses();
$total_items = 0;
$low_stock_items = [];
$recent_movements = $movement->getMovements(null, 5); // Get last 5 movements

// Calculate totals and get low stock items
foreach ($warehouses as $wh) {
    $items = $inventory->getInventoryByWarehouse($wh['warehouse_id']);
    foreach ($items as $item) {
        $total_items += $item['quantity'];
        if ($item['quantity'] < 10) { // Consider items with quantity < 10 as low stock
            $low_stock_items[] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="row p-4">
            <div class="col-12">
                <h1 class="mb-4">Dashboard</h1>
            </div>

            <!-- Summary Cards -->
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Warehouses</h5>
                        <h2><?php echo count($warehouses); ?></h2>
                        <i class="fas fa-warehouse fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card dashboard-card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Items</h5>
                        <h2><?php echo $total_items; ?></h2>
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card dashboard-card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Items</h5>
                        <h2><?php echo count($low_stock_items); ?></h2>
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card dashboard-card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Recent Movements</h5>
                        <h2><?php echo count($recent_movements); ?></h2>
                        <i class="fas fa-exchange-alt fa-2x"></i>
                    </div>
                </div>
            </div>

            <!-- Warehouses Table -->
            <div class="col-md-6 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Warehouse Overview</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Manager</th>
                                    <th>Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($warehouses as $wh): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($wh['name']); ?></td>
                                    <td><?php echo htmlspecialchars($wh['location']); ?></td>
                                    <td><?php echo htmlspecialchars($wh['manager_name']); ?></td>
                                    <td><?php echo htmlspecialchars($wh['capacity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="col-md-6 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Low Stock Alert</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Warehouse</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['warehouse_id']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td><span class="badge bg-danger">Low Stock</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Recent Movements</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_movements as $move): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($move['item_name']); ?></td>
                                    <td>
                                        <?php if ($move['movement_type'] == 'IN'): ?>
                                            <span class="badge bg-success">IN</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">OUT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($move['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($move['movement_date']); ?></td>
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
</body>
</html>