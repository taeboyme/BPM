<?php
session_start();
require_once('config/database.php');
require_once('vendor/autoload.php');
use Dompdf\Dompdf;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$run_id = $_GET['run_id'];

// Get payroll run details
$run_query = "SELECT * FROM payroll_runs WHERE run_id = ?";
$stmt = $conn->prepare($run_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();
$run = $stmt->get_result()->fetch_assoc();

if (!$run) {
    header("Location: payroll_runs.php");
    exit();
}

// Function to generate PDF payslip
function generatePayslipPDF($employee, $payslip, $components) {
    $dompdf = new Dompdf();
    
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .company { font-size: 24px; font-weight: bold; }
            .title { font-size: 18px; margin: 10px 0; }
            .info-table { width: 100%; margin-bottom: 20px; }
            .info-table td { padding: 5px; }
            .components-table { width: 100%; border-collapse: collapse; }
            .components-table th, .components-table td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left; 
            }
            .total-row { font-weight: bold; background-color: #f9f9f9; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="company">Company Name</div>
            <div class="title">Payslip for ' . date('F Y', strtotime($payslip['period_start'])) . '</div>
        </div>

        <table class="info-table">
            <tr>
                <td><strong>Employee ID:</strong> ' . $employee['employee_id'] . '</td>
                <td><strong>Name:</strong> ' . $employee['first_name'] . ' ' . $employee['last_name'] . '</td>
            </tr>
            <tr>
                <td><strong>Department:</strong> ' . $employee['department_name'] . '</td>
                <td><strong>Position:</strong> ' . $employee['position_name'] . '</td>
            </tr>
            <tr>
                <td><strong>Pay Period:</strong> ' . date('M d', strtotime($payslip['period_start'])) . 
                ' - ' . date('M d, Y', strtotime($payslip['period_end'])) . '</td>
                <td><strong>Pay Date:</strong> ' . date('M d, Y', strtotime($payslip['pay_date'])) . '</td>
            </tr>
        </table>

        <table class="components-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>';

    $total_earnings = 0;
    $total_deductions = 0;

    foreach ($components as $comp) {
        $html .= '<tr>
            <td>' . $comp['component_name'] . '</td>
            <td>$' . number_format($comp['amount'], 2) . '</td>
        </tr>';

        if ($comp['component_type'] == 'EARNING') {
            $total_earnings += $comp['amount'];
        } else {
            $total_deductions += $comp['amount'];
        }
    }

    $html .= '
                <tr class="total-row">
                    <td>Total Earnings</td>
                    <td>$' . number_format($total_earnings, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td>Total Deductions</td>
                    <td>$' . number_format($total_deductions, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td>Net Pay</td>
                    <td>$' . number_format($total_earnings - $total_deductions, 2) . '</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 12px;">
            <p>This is a computer-generated document. No signature is required.</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}

// Function to send email with PDF attachment
function sendPayslipEmail($employee, $pdf_content, $period) {
    $to = $employee['email'];
    $subject = "Payslip for " . date('F Y', strtotime($period));
    
    $boundary = md5(time());
    
    $headers = "From: payroll@company.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
    
    $message = "--" . $boundary . "\r\n";
    $message .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= "Dear " . $employee['first_name'] . ",\n\n";
    $message .= "Please find attached your payslip for " . date('F Y', strtotime($period)) . ".\n\n";
    $message .= "Best regards,\nPayroll Department\n\n";
    
    $message .= "--" . $boundary . "\r\n";
    $message .= "Content-Type: application/pdf; name=\"payslip.pdf\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"payslip.pdf\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= chunk_split(base64_encode($pdf_content)) . "\r\n";
    $message .= "--" . $boundary . "--";
    
    return mail($to, $subject, $message, $headers);
}

// Process payslips for the run
$payslip_query = "SELECT p.*, e.*, d.department_name, pos.position_name
                  FROM payslips p
                  JOIN employees e ON p.employee_id = e.employee_id
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  LEFT JOIN positions pos ON e.position_id = pos.position_id
                  WHERE p.run_id = ?";
$stmt = $conn->prepare($payslip_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();
$payslips = $stmt->get_result();

$success_count = 0;
$error_count = 0;

while ($payslip = $payslips->fetch_assoc()) {
    // Get salary components
    $comp_query = "SELECT sc.component_name, sc.component_type, ps.amount
                   FROM payslip_components ps
                   JOIN salary_components sc ON ps.component_id = sc.component_id
                   WHERE ps.payslip_id = ?
                   ORDER BY sc.component_type, sc.component_name";
    $stmt = $conn->prepare($comp_query);
    $stmt->bind_param("i", $payslip['payslip_id']);
    $stmt->execute();
    $components = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Generate PDF
    $pdf_content = generatePayslipPDF($payslip, $payslip, $components);
    
    // Send email
    if (sendPayslipEmail($payslip, $pdf_content, $payslip['period_start'])) {
        $success_count++;
    } else {
        $error_count++;
    }
}

// Update run status
$update_query = "UPDATE payroll_runs SET email_sent = 1 WHERE run_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();

// Redirect with status
header("Location: payroll_runs.php?email_sent=1&success=" . $success_count . "&error=" . $error_count);
exit();
?>