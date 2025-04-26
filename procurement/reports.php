<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Procurement System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Reports</h2>

    <!-- Report Filter -->
    <form method="POST" class="card p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Report Type</label>
                <select class="form-select" name="report_type">
                    <option value="purchase_orders">Purchase Orders</option>
                    <option value="inventory">Inventory Status</option>
                    <option value="movements">Stock Movements</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date Range</label>
                <input type="text" class="form-control" name="date_range" id="dateRange">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Generate</button>
                <button type="button" class="btn btn-success" id="printBtn" onclick = "window.print();">Print</button>
            </div>
        </div>
    </form>

    <!-- Report Output -->
    <div id="reportContent">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportType = $_POST['report_type'];
            [$startDate, $endDate] = array_map(fn($d) => date('Y-m-d', strtotime($d)), explode(' - ', $_POST['date_range'] ?? ''));

            $reportFiles = [
                'purchase_orders' => 'reports/purchase_orders_report.php',
                'inventory' => 'reports/inventory_report.php',
                'movements' => 'reports/movements_report.php',
            ];

            if (isset($reportFiles[$reportType])) {
                include $reportFiles[$reportType];
            }
        }
        ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables@1.10.24/js/jquery.dataTables.min.js"></script>

<script>
    $(function () {
        $('#dateRange').daterangepicker({
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('.report-table').DataTable();
    });
</script>
</body>
</html>
