<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$employee_id = $_GET['id'];

// Fetch employee details
$emp_query = "SELECT e.*, d.department_name, p.position_title 
              FROM employees e
              LEFT JOIN departments d ON e.department_id = d.department_id
              LEFT JOIN positions p ON e.position_id = p.position_id
              WHERE e.employee_id = ?";
$stmt = $conn->prepare($emp_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    header("Location: index.php");
    exit();
}

// Fetch current salary components
$current_query = "SELECT sc.component_name, sc.component_type, es.amount, es.effective_from
                 FROM employee_salary es
                 JOIN salary_components sc ON es.component_id = sc.component_id
                 WHERE es.employee_id = ?
                 AND (es.effective_to IS NULL OR es.effective_to >= CURDATE())
                 ORDER BY sc.component_type, sc.component_name";
$stmt = $conn->prepare($current_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$current_components = $stmt->get_result();

// Calculate current total salary
$total_query = "SELECT 
                SUM(CASE WHEN sc.component_type = 'EARNING' THEN es.amount ELSE 0 END) as total_earnings,
                SUM(CASE WHEN sc.component_type = 'DEDUCTION' THEN es.amount ELSE 0 END) as total_deductions
                FROM employee_salary es
                JOIN salary_components sc ON es.component_id = sc.component_id
                WHERE es.employee_id = ?
                AND (es.effective_to IS NULL OR es.effective_to >= CURDATE())";
$stmt = $conn->prepare($total_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();

// Fetch salary history
$history_query = "SELECT sc.component_name, sc.component_type, es.amount, 
                        es.effective_from, es.effective_to
                 FROM employee_salary es
                 JOIN salary_components sc ON es.component_id = sc.component_id
                 WHERE es.employee_id = ?
                 ORDER BY es.effective_from DESC, sc.component_type, sc.component_name";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$salary_history = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Salary Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Salary Details</h2>
            <div>
                <button onclick="window.print()" class="btn btn-secondary mr-2">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="index.php" class="btn btn-primary">Back to List</a>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Employee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Employee ID:</th>
                                <td><?php echo $employee['employee_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td><?php echo $employee['department_name']; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Position:</th>
                                <td><?php echo $employee['position_title']; ?></td>
                            </tr>
                            <tr>
                                <th>Hire Date:</th>
                                <td><?php echo date('M d, Y', strtotime($employee['hire_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-<?php echo $employee['employment_status'] == 'ACTIVE' ? 'success' : 'warning'; ?>">
                                        <?php echo $employee['employment_status']; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Salary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Current Salary Components</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Effective From</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($comp = $current_components->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $comp['component_name']; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $comp['component_type'] == 'EARNING' ? 'success' : 
                                            ($comp['component_type'] == 'DEDUCTION' ? 'danger' : 'info'); 
                                    ?>">
                                        <?php echo $comp['component_type']; ?>
                                    </span>
                                </td>
                                <td>₱<?php echo number_format($comp['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($comp['effective_from'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="table-active font-weight-bold">
                                <td colspan="2">Total Earnings</td>
                                <td colspan="2">₱<?php echo number_format($totals['total_earnings'], 2); ?></td>
                            </tr>
                            <tr class="table-active font-weight-bold">
                                <td colspan="2">Total Deductions</td>
                                <td colspan="2">₱<?php echo number_format($totals['total_deductions'], 2); ?></td>
                            </tr>
                            <tr class="table-active font-weight-bold">
                                <td colspan="2">Net Salary</td>
                                <td colspan="2">₱<?php echo number_format($totals['total_earnings'] - $totals['total_deductions'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Salary History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Salary History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historyTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Effective From</th>
                                <th>Effective To</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($history = $salary_history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $history['component_name']; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $history['component_type'] == 'EARNING' ? 'success' : 
                                            ($history['component_type'] == 'DEDUCTION' ? 'danger' : 'info'); 
                                    ?>">
                                        <?php echo $history['component_type']; ?>
                                    </span>
                                </td>
                                <td>₱<?php echo number_format($history['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($history['effective_from'])); ?></td>
                                <td>
                                    <?php 
                                    echo $history['effective_to'] 
                                        ? date('M d, Y', strtotime($history['effective_to'])) 
                                        : 'Current';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
            order: [[3, 'desc']],
            pageLength: 25
        });
    });
    </script>
</body>
</html>