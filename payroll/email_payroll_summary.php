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
$run_query = "SELECT pr.*, u.full_name as processed_by
              FROM payroll_runs pr
              LEFT JOIN users u ON pr.processed_by = u.user_id
              WHERE pr.run_id = ?";
$stmt = $conn->prepare($run_query);
$stmt->bind_param("i", $run_id);
$stmt->execute();
$run = $stmt->get_result()->fetch_assoc();

if (!$run) {
    header("Location: payroll_runs.php");
    exit();
}

// Generate summary report
function generateSummaryPDF($run, $conn) {
    $dompdf = new Dompdf();
    
    // Get department summaries
    $dept_query = "SELECT d.department_name,
                    COUNT(DISTINCT p.employee_id) as emp_count,
                    SUM(p.gross_pay) as total_gross,
                    SUM(p.net_pay) as total_net,
                    SUM(p.tax_deducted) as total_tax
                   FROM payslips p
                   JOIN employees e ON p.employee_id = e.employee_id
                   JOIN departments d ON e.department_id = d.department_id
                   WHERE p.run_id = ?
                   GROUP BY d.department_id";
    $stmt = $conn->prepare($dept_query);
    $stmt->bind_param("i", $run['run_id']);
    $stmt->execute();
    $departments = $stmt->get_result();

    // Get component summaries
    $comp_query = "SELECT sc.component_name, sc.component_type,
                    COUNT(DISTINCT pc.payslip_id) as usage_count,
                    SUM(pc.amount) as total_amount
                   FROM payslip_components pc
                   JOIN salary_components sc ON pc.component_id = sc.component_id
                   JOIN payslips p ON pc.payslip_id = p.payslip_id
                   WHERE p.run_id = ?
                   GROUP BY sc.component_id";
    $stmt = $conn->prepare($comp_query);
    $stmt->bind_param("i", $run['run_id']);
    $stmt->execute();
    $components = $stmt->get_result();

    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 20px; }
            .title { font-size: 24px; font-weight: bold; }
            .subtitle { font-size: 16px; margin: 10px 0; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f5f5f5; }
            .total-row { font-weight: bold; background-color: #f9f9f9; }
            .section { margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">Payroll Summary Report</div>
            <div class="subtitle">Pay Period: ' . date('M d', strtotime($run['period_start'])) . 
            ' - ' . date('M d, Y', strtotime($run['period_end'])) . '</div>
        </div>

        <div class="section">
            <h3>Run Details</h3>
            <table>
                <tr>
                    <td><strong>Run ID:</strong> ' . $run['run_id'] . '</td>
                    <td><strong>Status:</strong> ' . $run['status'] . '</td>
                </tr>
                <tr>
                    <td><strong>Processed By:</strong> ' . $run['processed_by'] . '</td>
                    <td><strong>Process Date:</strong> ' . date('M d, Y', strtotime($run['processed_at'])) . '</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h3>Department Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Employees</th>
                        <th>Gross Pay</th>
                        <th>Tax</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>';

    $total_employees = 0;
    $total_gross = 0;
    $total_tax = 0;
    $total_net = 0;

    while ($dept = $departments->fetch_assoc()) {
        $html .= '<tr>
            <td>' . $dept['department_name'] . '</td>
            <td>' . $dept['emp_count'] . '</td>
            <td>$' . number_format($dept['total_gross'], 2) . '</td>
            <td>$' . number_format($dept['total_tax'], 2) . '</td>
            <td>$' . number_format($dept['total_net'], 2) . '</td>
        </tr>';

        $total_employees += $dept['emp_count'];
        $total_gross += $dept['total_gross'];
        $total_tax += $dept['total_tax'];
        $total_net += $dept['total_net'];
    }

    $html .= '<tr class="total-row">
            <td>Total</td>
            <td>' . $total_employees . '</td>
            <td>$' . number_format($total_gross, 2) . '</td>
            <td>$' . number_format($total_tax, 2) . '</td>
            <td>$' . number_format($total_net, 2) . '</td>
        </tr>
        </tbody>
        </table>
        </div>

        <div class="section">
            <h3>Salary Components</h3>
            <table>
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Type</th>
                        <th>Usage Count</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>';

    while ($comp = $components->fetch_assoc()) {
        $html .= '<tr>
            <td>' . $comp['component_name'] . '</td>
            <td>' . $comp['component_type'] . '</td>
            <td>' . $comp['usage_count'] . '</td>
            <td>$' . number_format($comp['total_amount'], 2) . '</td>
        </tr>';
    }

    $html .= '</tbody>
        </table>
        </div>

        <div style="margin-top: 30px; font-size: 12px;">
            <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}

// Send summary email
function sendSummaryEmail($run, $pdf_content) {
    // Get recipients from configuration
    $recipients = [
        'finance@company.com',
        'hr@company.com',
        'management@company.com'
    ];

    $to = implode(', ', $recipients);
    $subject = "Payroll Summary Report - " . date('F Y', strtotime($run['period_start']));
    
    $boundary = md5(time());
    
    $headers = "From: payroll.system@company.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";
    
    $message = "--" . $boundary . "\r\n";
    $message .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= "Please find attached the payroll summary report for " . 
                date('F Y', strtotime($run['period_start'])) . ".\n\n";
    $message .= "Run Details:\n";
    $message .= "- Run ID: " . $run['run_id'] . "\n";
    $message .= "- Period: " . date('M d', strtotime($run['period_start'])) . 
                " - " . date('M d, Y', strtotime($run['period_end'])) . "\n";
    $message .= "- Processed By: " . $run['processed_by'] . "\n";
    $message .= "- Status: " . $run['status'] . "\n\n";
    $message .= "Best regards,\nPayroll System\n\n";
    
    $message .= "--" . $boundary . "\r\n";
    $message .= "Content-Type: application/pdf; name=\"payroll_summary.pdf\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"payroll_summary.pdf\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= chunk_split(base64_encode($pdf_content)) . "\r\n";
    $message .= "--" . $boundary . "--";
    
    return mail($to, $subject, $message, $headers);
}

try {
    // Generate PDF
    $pdf_content = generateSummaryPDF($run, $conn);
    
    // Send email
    if (sendSummaryEmail($run, $pdf_content)) {
        // Update run status
        $update_query = "UPDATE payroll_runs 
                        SET summary_sent = 1,
                            summary_sent_at = NOW()
                        WHERE run_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $run_id);
        $stmt->execute();

        header("Location: payroll_runs.php?summary_sent=1");
    } else {
        throw new Exception("Failed to send summary email");
    }

} catch (Exception $e) {
    // Log error
    $error_log = "INSERT INTO error_logs 
                  (error_type, error_message, error_details, occurred_at)
                  VALUES ('SUMMARY_EMAIL', ?, ?, NOW())";
    $stmt = $conn->prepare($error_log);
    $error_msg = $e->getMessage();
    $error_details = json_encode(['run_id' => $run_id]);
    $stmt->bind_param("ss", $error_msg, $error_details);
    $stmt->execute();

    header("Location: payroll_runs.php?error=summary_email");
}

exit();
?>