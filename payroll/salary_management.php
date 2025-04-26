<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all salary components
$components_query = "SELECT * FROM salary_components";
$components = $conn->query($components_query);

// Fetch all employees with their current salary
$employee_query = "SELECT e.employee_id, e.first_name, e.last_name,
                    (SELECT SUM(amount) FROM employee_salary 
                     WHERE employee_id = e.employee_id 
                     AND (effective_to IS NULL OR effective_to >= CURDATE())
                    ) as current_salary
                  FROM employees e
                  WHERE e.employment_status = 'ACTIVE'
                  ORDER BY e.last_name, e.first_name";
$employees = $conn->query($employee_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $component_id = $_POST['component_id'];
    $amount = $_POST['amount'];
    $effective_from = $_POST['effective_from'];
    
    // Set effective_to date for current component if exists
    $update_query = "UPDATE employee_salary 
                    SET effective_to = DATE_SUB(?, INTERVAL 1 DAY)
                    WHERE employee_id = ? 
                    AND component_id = ?
                    AND effective_to IS NULL";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $effective_from, $employee_id, $component_id);
    $stmt->execute();
    
    // Insert new salary component
    $insert_query = "INSERT INTO employee_salary 
                    (employee_id, component_id, amount, effective_from) 
                    VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sids", $employee_id, $component_id, $amount, $effective_from);
    
    if ($stmt->execute()) {
        header("Location: salary_management.php?success=1");
        exit();
    } else {
        $error = "Error updating salary: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Salary Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Salary Management</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Salary updated successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Salary Assignment Form -->
        <div class="card mb-4">
            <div class="card-header">
                Assign Salary Component
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    <?php 
                                    $employees->data_seek(0);
                                    while($emp = $employees->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $emp['employee_id']; ?>">
                                            <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Salary Component</label>
                                <select name="component_id" class="form-control" required>
                                    <option value="">Select Component</option>
                                    <?php 
                                    // Reset pointer for components data if needed
                                    $components->data_seek(0);
                                    while($comp = $components->fetch_assoc()): ?>
                                        <option value="<?php echo $comp['component_id']; ?>">
                                            <?php echo $comp['component_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Effective From</label>
                                <input type="date" name="effective_from" class="form-control" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Salary Component</button>
                </form>
            </div>
        </div>

        <!-- Employee Salary List -->
        <div class="card">
            <div class="card-header">
                Current Employee Salaries
            </div>
            <div class="card-body">
                <table id="salaryTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Current Salary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $employees->data_seek(0);
                        while($emp = $employees->fetch_assoc()): 
                            // Disable the view history button if current salary is empty or zero
                            $isDisabled = empty($emp['current_salary']) || $emp['current_salary'] == 0;
                        ?>
                        <tr>
                            <td><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></td>
                            <td>₱<?php echo number_format($emp['current_salary'] ?? 0, 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" 
                                        onclick="viewHistory('<?php echo $emp['employee_id']; ?>')" 
                                        <?php echo $isDisabled ? 'disabled' : ''; ?>>
                                    View History
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Salary History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Salary History</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="historyContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#salaryTable').DataTable();
    });

    function viewHistory(employeeId) {
        $.get('get_salary_history.php', { employee_id: employeeId }, function(data) {
            $('#historyContent').html(data);
            $('#historyModal').modal('show');
        });
    }
    </script>
</body>
</html>
