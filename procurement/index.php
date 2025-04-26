<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Procurement System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Dashboard</h2>

    <div class="row mt-4">
        <!-- Purchase Requests -->
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Purchase Requests</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM purchase_requests WHERE status = 'PENDING'";
                    $result = $conn->query($sql);
                    $count = $result->fetch_assoc()['count'];
                    ?>
                    <p class="card-text display-4"><?php echo $count; ?></p>
                    <a href="purchase_requests.php" class="text-white">View Requests <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Purchase Orders -->
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Pending Purchase Orders</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM purchase_orders WHERE status = 'PENDING'";
                    $result = $conn->query($sql);
                    $count = $result->fetch_assoc()['count'];
                    ?>
                    <p class="card-text display-4"><?php echo $count; ?></p>
                    <a href="purchase_orders.php" class="text-white">View Orders <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Items</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as count FROM inventory_items WHERE quantity < 10";
                    $result = $conn->query($sql);
                    $count = $result->fetch_assoc()['count'];
                    ?>
                    <p class="card-text display-4"><?php echo $count; ?></p>
                    <a href="inventory.php" class="text-white">Check Inventory <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities & System Stats Side-by-Side -->
    <div class="row mt-4">
        <!-- Recent Activities -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Inventory Movements</h5>
                    <div class="list-group">
                        <?php
                        $sql = "SELECT * FROM inventory_movements ORDER BY movement_date DESC LIMIT 5";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <?php echo htmlspecialchars($row['movement_type']); ?> - <?php echo $row['quantity']; ?> units
                            <small class="text-muted d-block"><?php echo date('M d, Y H:i', strtotime($row['movement_date'])); ?></small>
                        </a>
                        <?php endwhile; else: ?>
                        <p class="text-muted">No recent activity.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Stats -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">System Statistics</h5>
                    <?php
                    $stats = [
                        "SELECT COUNT(*) FROM purchase_requests" => "Total Purchase Requests",
                        "SELECT COUNT(*) FROM purchase_orders" => "Total Purchase Orders",
                        "SELECT COUNT(*) FROM inventory_items" => "Total Inventory Items"
                    ];
                    foreach($stats as $query => $label):
                        $result = $conn->query($query);
                        $count = $result->fetch_row()[0];
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php echo $label; ?>:</span>
                        <strong><?php echo $count; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
