<?php
session_start();
require_once('config/database.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $period_start = $_POST['period_start'];
    $period_end = $_POST['period_end'];
    $run_date = date('Y-m-d');
    
    // Create new payroll run
    $query = "INSERT INTO payroll_runs (period_start, period_end, run_date, status) 
              VALUES (?, ?, ?, 'PROCESSING')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $period_start, $period_end, $run_date);
    $stmt->execute();
    $run_id = $conn->insert_id;
    
    // Calculate payroll for each employee
    $emp_query = "SELECT employee_id FROM employees WHERE employment_status = 'ACTIVE'";
    $emp_result = $conn->query($emp_query);
    
    while ($employee = $emp_result->fetch_assoc()) {
        // Calculate gross pay
        $salary_query = "SELECT SUM(amount) as total FROM employee_salary 
                        WHERE employee_id = ? AND effective_to IS NULL";
        $stmt = $conn->prepare($salary_query);
        $stmt->bind_param("s", $employee['employee_id']);
        $stmt->execute();
        $salary_result = $stmt->get_result()->fetch_assoc();
        
        $gross_pay = $salary_result['total'];
        $deductions = $gross_pay * 0.12; // Example: 12% deductions
        $net_pay = $gross_pay - $deductions;
        
        // Create payslip
        $payslip_query = "INSERT INTO payslips (run_id, employee_id, gross_pay, deductions, net_pay, generated_date)
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($payslip_query);
        $stmt->bind_param("isddds", $run_id, $employee['employee_id'], $gross_pay, $deductions, $net_pay, $run_date);
        $stmt->execute();
    }
    
    // Update run status
    $update_query = "UPDATE payroll_runs SET status = 'COMPLETED' WHERE run_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $run_id);
    $stmt->execute();
    
    header("Location: view_payroll_run.php?id=" . $run_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Run Payroll</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h2>Run Payroll</h2>
        <br/>
        <form method="POST">
            <div class="form-group">
                <label>Period Start</label>
                <input type="date" name="period_start" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Period End</label>
                <input type="date" name="period_end" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Process Payroll</button>
        </form>
    </div>
</body>
</html>