<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user role and name
$user_id = $_SESSION['user_id'];
$query = "SELECT first_name, last_name, role, username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Payroll System
        </a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-users"></i> Employees
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="view_employees.php">View All Employees</a>
                        <a class="dropdown-item" href="add_employee.php">Add New Employee</a>
                        <a class="dropdown-item" href="salary_management.php">Salary Management</a>
                    </div>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-money-check-alt"></i> Payroll
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="run_payroll.php">Run Payroll</a>
                        <a class="dropdown-item" href="payroll_history.php">Payroll History</a>
                        <a class="dropdown-item" href="salary_components.php">Salary Components</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="payroll_summary.php">Payroll Summary</a>
                        <a class="dropdown-item" href="employee_reports.php">Employee Reports</a>
                    </div>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>&nbsp; 
                        <?php echo $user['username']; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a class="dropdown-item" href="change_password.php">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/your-kit-code.js"></script>

<script>
$(document).ready(function() {
    // Highlight active menu item
    var currentPage = window.location.pathname.split('/').pop();
    $('.nav-link').each(function() {
        var href = $(this).attr('href');
        if (href === currentPage) {
            $(this).addClass('active');
            $(this).closest('.dropdown').find('.dropdown-toggle').addClass('active');
        }
    });
});
</script>