<?php
session_start();
require_once('config/database.php');


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$payslip_id = $_GET['id'];

$query = "SELECT p.*, e.first_name, e.last_name, r.period_start, r.period_end
          FROM payslips p
          JOIN employees e ON p.employee_id = e.employee_id
          JOIN payroll_runs r ON p.run_id = r.run_id
          WHERE p.payslip_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $payslip_id);
$stmt->execute();
$payslip = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h2>Payslip</h2>
        <br/>
        <div class="card">
            <div class="card-body">
                <h5>Employee: <?php echo $payslip['first_name'] . ' ' . $payslip['last_name']; ?></h5>
                <p>Period: <?php echo $payslip['period_start'] . ' to ' . $payslip['period_end']; ?></p>
                
                <table class="table">
                    <tr>
                        <td>Gross Pay</td>
                        <td>$<?php echo number_format($payslip['gross_pay'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Deductions</td>
                        <td>$<?php echo number_format($payslip['deductions'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Net Pay</strong></td>
                        <td><strong>$<?php echo number_format($payslip['net_pay'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <a href="javascript:window.print()" class="btn btn-primary mt-3">Print Payslip</a>
    </div>
</body>
</html>