<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$run_id = $_GET['run_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if run exists and can be cancelled
    $check_query = "SELECT * FROM payroll_runs WHERE run_id = ? AND status NOT IN ('CANCELLED', 'COMPLETED')";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $run_id);
    $stmt->execute();
    $run = $stmt->get_result()->fetch_assoc();

    if (!$run) {
        throw new Exception("Invalid payroll run or cannot be cancelled.");
    }

    // Delete payslip components
    $delete_components = "DELETE pc FROM payslip_components pc
                         JOIN payslips p ON pc.payslip_id = p.payslip_id
                         WHERE p.run_id = ?";
    $stmt = $conn->prepare($delete_components);
    $stmt->bind_param("i", $run_id);
    $stmt->execute();

    // Delete payslips
    $delete_payslips = "DELETE FROM payslips WHERE run_id = ?";
    $stmt = $conn->prepare($delete_payslips);
    $stmt->bind_param("i", $run_id);
    $stmt->execute();

    // Revert any bank transactions
    $revert_transactions = "UPDATE bank_transactions 
                          SET status = 'CANCELLED', 
                              cancelled_at = NOW(),
                              cancelled_by = ?
                          WHERE payroll_run_id = ?";
    $stmt = $conn->prepare($revert_transactions);
    $user_id = $_SESSION['user_id']; // Assuming user session exists
    $stmt->bind_param("ii", $user_id, $run_id);
    $stmt->execute();

    // Update run status
    $update_run = "UPDATE payroll_runs 
                   SET status = 'CANCELLED',
                       cancelled_at = NOW(),
                       cancelled_by = ?,
                       cancellation_reason = ?
                   WHERE run_id = ?";
    $stmt = $conn->prepare($update_run);
    $reason = isset($_POST['reason']) ? $_POST['reason'] : 'Manual cancellation';
    $stmt->bind_param("isi", $user_id, $reason, $run_id);
    $stmt->execute();

    // Log the cancellation
    $log_query = "INSERT INTO payroll_logs 
                  (run_id, action, description, performed_by, performed_at)
                  VALUES (?, 'CANCEL', ?, ?, NOW())";
    $stmt = $conn->prepare($log_query);
    $description = "Payroll run cancelled. Reason: " . $reason;
    $stmt->bind_param("isi", $run_id, $description, $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Redirect with success message
    header("Location: payroll_runs.php?cancelled=1");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Log the error
    $error_log = "INSERT INTO error_logs 
                  (error_type, error_message, error_details, occurred_at)
                  VALUES ('PAYROLL_CANCEL', ?, ?, NOW())";
    $stmt = $conn->prepare($error_log);
    $error_msg = $e->getMessage();
    $error_details = json_encode([
        'run_id' => $run_id,
        'user_id' => $user_id,
        'trace' => $e->getTraceAsString()
    ]);
    $stmt->bind_param("ss", $error_msg, $error_details);
    $stmt->execute();

    // Redirect with error message
    header("Location: payroll_runs.php?error=cancel");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cancel Payroll Run</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Cancel Payroll Run</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="cancel_payroll_run.php?run_id=<?php echo $run_id; ?>" 
                              onsubmit="return confirm('Are you sure you want to cancel this payroll run? This action cannot be undone.');">
                            <div class="form-group">
                                <label>Cancellation Reason</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-danger">Cancel Payroll Run</button>
                                <a href="payroll_runs.php" class="btn btn-secondary">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>