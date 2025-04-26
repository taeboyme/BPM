<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Report.php';
require_once 'classes/Warehouse.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$report = new Report($conn);
$warehouse = new Warehouse($conn);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$selected_warehouse = isset($_GET['warehouse_id']) ? $_GET['warehouse_id'] : null;

// Get report data
$stock_summary = $report->getStockSummary($selected_warehouse);
$movement_summary = $report->getMovementSummary($start_date, $end_date, $selected_warehouse);
$low_stock_items = $report->getLowStockItems($selected_warehouse);
$warehouses = $warehouse->getWarehouses();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - Warehouse Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Warehouse</label>
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
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" 
                               value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" 
                               value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Items</h5>
                        <h2><?php echo $stock_summary['total_items']; ?></h2>
                        <p class="mb-0">Unique products in inventory</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Stock In</h5>
                        <h2><?php echo $movement_summary['total_in']; ?></h2>
                        <p class="mb-0">Items received this period</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Stock Out</h5>
                        <h2><?php echo $movement_summary['total_out']; ?></h2>
                        <p class="mb-0">Items dispatched this period</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Items</h5>
                        <h2><?php echo count($low_stock_items); ?></h2>
                        <p class="mb-0">Items requiring attention</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Stock Movement Trend</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="movementChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Warehouse Capacity Usage</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="capacityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items Table -->
        <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Low Stock Items</h5>
        <div id="buttonContainer"></div>
    </div>
    <div class="card-body">
        <table id="lowStockTable" class="table table-striped">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Warehouse</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($low_stock_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['warehouse_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']) . ' ' . htmlspecialchars($item['unit']); ?></td>
                        <td>
                            <?php if ($item['quantity'] == 0): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Low Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="stock_in.php?id=<?php echo $item['item_id']; ?>" 
                               class="btn btn-sm btn-success">
                                <i class="fas fa-plus-circle"></i> Restock
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Required JS Scripts -->

<!-- DataTables Buttons Extensions -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

<!-- Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Initialize Low Stock Table with Excel Export Button
    $(document).ready(function () {
        const table = $('#lowStockTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-2"></i> Export to Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [0, 1, 2, 3] // Exclude Actions column
                    }
                }
            ],
            pageLength: 10,
            order: [[2, "asc"]]
        });

        // Move buttons to custom container
        table.buttons().container().appendTo('#buttonContainer');
    });

    // Movement Trend Chart
    const movementCtx = document.getElementById('movementChart').getContext('2d');
    new Chart(movementCtx, {
        type: 'line',
        data: <?php echo json_encode($report->getMovementTrendData($start_date, $end_date, $selected_warehouse)); ?>,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Warehouse Capacity Chart
    const capacityCtx = document.getElementById('capacityChart').getContext('2d');
    new Chart(capacityCtx, {
        type: 'bar',
        data: <?php echo json_encode($report->getWarehouseCapacityData($selected_warehouse)); ?>,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
</script>
</body>
</html>