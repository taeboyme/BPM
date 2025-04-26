<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters
$query = "SELECT pr.*, 
            COUNT(p.payslip_id) as employee_count,
            SUM(p.gross_pay) as total_gross,
            SUM(p.net_pay) as total_net
          FROM payroll_runs pr
          LEFT JOIN payslips p ON pr.run_id = p.run_id
          WHERE YEAR(pr.run_date) = ?
          " . ($status ? "AND pr.status = ?" : "") . "
          GROUP BY pr.run_id
          ORDER BY pr.run_date DESC";

$stmt = $conn->prepare($query);
if ($status) {
    $stmt->bind_param("is", $year, $status);
} else {
    $stmt->bind_param("i", $year);
}
$stmt->execute();
$payroll_runs = $stmt->get_result();

// Get available years for filter
$years_query = "SELECT DISTINCT YEAR(run_date) as year FROM payroll_runs ORDER BY year DESC";
$years = $conn->query($years_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payroll History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
   <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Payroll History</h2>
            <a href="run_payroll.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Payroll Run
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Year:</label>
                        <select name="year" class="form-control">
                            <?php while($year_row = $years->fetch_assoc()): ?>
                                <option value="<?php echo $year_row['year']; ?>"
                                        <?php echo $year_row['year'] == $year ? 'selected' : ''; ?>>
                                    <?php echo $year_row['year']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group mr-3">
                        <label class="mr-2">Status:</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="PROCESSING" <?php echo $status == 'PROCESSING' ? 'selected' : ''; ?>>Processing</option>
                            <option value="COMPLETED" <?php echo $status == 'COMPLETED' ? 'selected' : ''; ?>>Completed</option>
                            <option value="CANCELLED" <?php echo $status == 'CANCELLED' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- Payroll Runs Table -->
        <div class="card">
            <div class="card-body">
                <table id="historyTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Run Date</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Employees</th>
                            <th>Total Gross</th>
                            <th>Total Net</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($run = $payroll_runs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($run['run_date'])); ?></td>
                            <td>
                                <?php 
                                echo date('M d', strtotime($run['period_start'])) . ' - ' . 
                                     date('M d, Y', strtotime($run['period_end'])); 
                                ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $run['status'] == 'COMPLETED' ? 'success' : 
                                        ($run['status'] == 'PROCESSING' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo $run['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $run['employee_count']; ?></td>
                            <td>₱<?php echo number_format($run['total_gross'], 2); ?></td>
                            <td>₱<?php echo number_format($run['total_net'], 2); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="view_payroll_run.php?id=<?php echo $run['run_id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if($run['status'] == 'PROCESSING'): ?>
                                    <button onclick="cancelRun(<?php echo $run['run_id']; ?>)" 
                                            class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="emailSummary(<?php echo $run['run_id']; ?>)" 
                                            class="btn btn-sm btn-secondary">
                                        <i class="fas fa-envelope"></i> Email
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#historyTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });

    function cancelRun(runId) {
        if (confirm('Are you sure you want to cancel this payroll run?')) {
            $.post('cancel_payroll_run.php', { run_id: runId }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error cancelling payroll run');
                }
            }, 'json');
        }
    }

    function emailSummary(runId) {
        $.post('email_payroll_summary.php', { run_id: runId }, function(response) {
            alert('Payroll summary has been emailed successfully!');
        });
    }
    </script>
</body>
</html>