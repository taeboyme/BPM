<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Movement.php';
require_once 'classes/Inventory.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$movement = new Movement($conn);
$inventory = new Inventory($conn);

// Get item ID if specified
$item_id = isset($_GET['id']) ? $_GET['id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get movements based on filters
$movements = $movement->getMovements($item_id, $start_date, $end_date);

// Get item details if specific item is selected
$item = null;
if ($item_id) {
    $item = $inventory->getItemById($item_id);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movement History - Warehouse Management System</title>
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
            <h2>
                <i class="fas fa-history me-2"></i>
                <?php echo $item ? 'Movement History for ' . htmlspecialchars($item['item_name']) : 'Movement History'; ?>
            </h2>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </div>

        <?php if ($item): ?>
            <div class="alert alert-info mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Item:</strong> <?php echo htmlspecialchars($item['item_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Current Stock:</strong> <?php echo htmlspecialchars($item['quantity']); ?> <?php echo htmlspecialchars($item['unit']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Warehouse:</strong> <?php echo htmlspecialchars($item['warehouse_name']); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table id="movementTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <?php if (!$item): ?>
                                <th>Item</th>
                                <th>Warehouse</th>
                            <?php endif; ?>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $move): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($move['movement_date'] ?? '')); ?></td>
                                <?php if (!$item): ?>
                                    <td><?php echo htmlspecialchars($move['item_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($move['warehouse_name'] ?? 'N/A'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if (($move['movement_type'] ?? '') == 'IN'): ?>
                                        <span class="badge bg-success">Stock In</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Stock Out</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($move['movement_type'] ?? '') == 'IN'): ?>
                                        <span class="text-success">+<?php echo htmlspecialchars($move['quantity'] ?? '0'); ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">-<?php echo htmlspecialchars($move['quantity'] ?? '0'); ?></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($move['unit'] ?? ''); ?>
                                </td>
                                <td><?php echo htmlspecialchars($move['reference'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Movements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET">
                    <?php if ($item_id): ?>
                        <input type="hidden" name="id" value="<?php echo $item_id; ?>">
                    <?php endif; ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#movementTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 25,
                "language": {
                    "search": "Search movements:"
                }
            });
        });
    </script>
</body>
</html>
