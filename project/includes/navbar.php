<?php
if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit();
}

// Get user role and name
$emp_query = "SELECT e.*, d.department_name
              FROM employees e 
              LEFT JOIN departments d ON e.department_id = d.department_id 
              WHERE e.employee_id = ?";
$emp_stmt = $conn->prepare($emp_query);
$emp_stmt->bind_param("s", $employee_id);
$emp_stmt->execute();
$employee = $emp_stmt->get_result()->fetch_assoc();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Project Management System
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
                <li class="nav-item">
                    <a class="nav-link" href="projects.php">
                        <i class="fas fa-briefcase"></i> Projects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="task.php">
                        <i class="fas fa-tasks"></i> Task
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="departments.php">
                        <i class="fas fa-users"></i> Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>&nbsp; 
                        <?php echo $employee['first_name']; ?>
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