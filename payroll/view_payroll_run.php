<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$run_id = $_GET['id'];

// Fetch payroll run details
$run_query = "SELECT * FROM payroll_runs WHERE run_id = ?";
$stmt = $conn->prepare($run_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();
$run = $stmt->get_result()->fetch_assoc();

// Fetch payslips for this run
$payslip_query = "SELECT p.*, e.first_name, e.last_name 
                  FROM payslips p
                  JOIN employees e ON p.employee_id = e.employee_id
                  WHERE p.run_id = ?
                  ORDER BY e.last_name, e.first_name";
$stmt = $conn->prepare($payslip_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();
$payslips = $stmt->get_result();

// Calculate totals
$totals_query = "SELECT 
                    COUNT(*) as total_employees,
                    SUM(gross_pay) as total_gross,
                    SUM(deductions) as total_deductions,
                    SUM(net_pay) as total_net
                 FROM payslips WHERE run_id = ?";
$stmt = $conn->prepare($totals_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Payroll Run</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Payroll Run Details</h2>
            <div>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="payroll_history.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
            </div>
        </div>

        <!-- Payroll Run Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Run Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Run Date:</th>
                                <td><?php echo date('F d, Y', strtotime($run['run_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Period:</th>
                                <td>
                                    <?php 
                                    echo date('M d', strtotime($run['period_start'])) . ' - ' . 
                                         date('M d, Y', strtotime($run['period_end'])); 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-<?php echo $run['status'] == 'COMPLETED' ? 'success' : 'warning'; ?>">
                                        <?php echo $run['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Total Employees:</th>
                                <td><?php echo $totals['total_employees']; ?></td>
                            </tr>
                            <tr>
                                <th>Total Gross Pay:</th>
                                <td>₱<?php echo number_format($totals['total_gross'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Total Deductions:</th>
                                <td>₱<?php echo number_format($totals['total_deductions'], 2); ?></td>
                            </tr>
                            <tr>
                                <th>Total Net Pay:</th>
                                <td>₱<?php echo number_format($totals['total_net'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payslips Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Employee Payslips</h5>
            </div>
            <div class="card-body">
                <table id="payslipsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($payslip = $payslips->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $payslip['first_name'] . ' ' . $payslip['last_name']; ?></td>
                            <td>₱<?php echo number_format($payslip['gross_pay'], 2); ?></td>
                            <td>₱<?php echo number_format($payslip['deductions'], 2); ?></td>
                            <td>₱<?php echo number_format($payslip['net_pay'], 2); ?></td>
                            <td>
                                <a href="view_payslip.php?id=<?php echo $payslip['payslip_id']; ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button class="btn btn-sm btn-secondary" 
                                        onclick="emailPayslip(<?php echo $payslip['payslip_id']; ?>)">
                                    <i class="fas fa-envelope"></i> Email
                                </button>
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
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#payslipsTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Export to Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                }
            ]
        });
    });

    function emailPayslip(payslipId) {
        // Implement email functionality
        $.post('email_payslip.php', { payslip_id: payslipId }, function(response) {
            alert('Payslip has been emailed successfully!');
        });
    }
    </script>
</body>
</html>