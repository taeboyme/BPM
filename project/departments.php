<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fetch employee data
$query = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Fetch departments with joins
$query = "
    SELECT d.*, 
           CONCAT(e.first_name, ' ', e.last_name) AS head_name, 
           pd.department_name AS parent_name,
           COUNT(p.project_id) AS project_count
    FROM departments d
    LEFT JOIN employees e ON d.department_head_id = e.employee_id
    LEFT JOIN departments pd ON d.parent_department_id = pd.department_id
    LEFT JOIN projects p ON d.department_id = p.department_id
    GROUP BY d.department_id
";
$departments = $conn->query($query);

// Fetch all departments for dropdowns
$allDepartments = $conn->query("SELECT department_id, department_name FROM departments");
$allDepartmentsForEdit = $conn->query("SELECT department_id, department_name FROM departments");

// Fetch all employees for head selection
$employees = $conn->query("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS name FROM employees");

// Handle deletion with dependency check
if (isset($_POST['delete_department'])) {
    $dept_id = intval($_POST['department_id']);

    // Check if the department is referenced by child departments or projects
    $check = $conn->query("
        SELECT 1 FROM departments WHERE parent_department_id = $dept_id
        UNION
        SELECT 1 FROM projects WHERE department_id = $dept_id
    ");

    if ($check && $check->num_rows > 0) {
        echo "<script>alert('Cannot delete department. It is referenced by other records.'); window.location='departments.php';</script>";
        exit();
    }

    // Perform deletion
    if ($conn->query("DELETE FROM departments WHERE department_id = $dept_id")) {
        echo "<script>alert('Department deleted successfully.'); window.location='departments.php';</script>";
    } else {
        echo "<script>alert('Error deleting department.'); window.location='departments.php';</script>";
    }
    exit();
}

// Handle addition
if (isset($_POST['add_department'])) {
    $name = $conn->real_escape_string($_POST['department_name']);
    $head_id = $_POST['department_head_id'] ? "'".$conn->real_escape_string($_POST['department_head_id'])."'" : "NULL";
    $parent_id = $_POST['parent_department_id'] ? intval($_POST['parent_department_id']) : "NULL";

    if ($conn->query("INSERT INTO departments (department_name, department_head_id, parent_department_id) VALUES ('$name', $head_id, $parent_id)")){
        echo "<script>alert('Department added successfully.'); window.location='departments.php';</script>";
    } else { 
        echo "<script>alert('Error adding department.'); window.location='departments.php';</script>";
    }
    exit();
}

// Handle editing
if (isset($_POST['edit_department'])) {
    $id = intval($_POST['edit_department_id']);
    $name = $conn->real_escape_string($_POST['edit_department_name']);
    $head_id = $_POST['edit_department_head_id'] ? "'".$conn->real_escape_string($_POST['edit_department_head_id'])."'" : "NULL";
    $parent_id = $_POST['edit_parent_department_id'] ? intval($_POST['edit_parent_department_id']) : "NULL";

    if ($conn->query("UPDATE departments 
                  SET department_name = '$name', 
                      department_head_id = $head_id, 
                      parent_department_id = $parent_id 
                  WHERE department_id = $id")){
        echo "<script>alert('Department updated successfully.'); window.location='departments.php';</script>";
    }else{
        echo "<script>alert('Error updating department.'); window.location='departments.php';</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Departments - Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Departments</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">Add Department</button>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-bordered table-secondary">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Head</th>
                    <th>Parent Department</th>
                    <th>Projects</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while($dept = $departments->fetch_assoc()): ?>
                    <tr>
                        <td><?= $dept['department_id']; ?></td>
                        <td><?= htmlspecialchars($dept['department_name']); ?></td>
                        <td><?= $dept['head_name'] ?? 'N/A'; ?></td>
                        <td><?= $dept['parent_name'] ?? 'None'; ?></td>
                        <td><?= $dept['project_count']; ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-dept"
                                    data-id="<?= $dept['department_id']; ?>"
                                    data-name="<?= htmlspecialchars($dept['department_name']); ?>"
                                    data-head="<?= $dept['department_head_id']; ?>"
                                    data-parent="<?= $dept['parent_department_id']; ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editDepartmentModal"><i class="fas fa-pen"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                <input type="hidden" name="department_id" value="<?= $dept['department_id']; ?>">
                                <button type="submit" name="delete_department" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Department Name</label>
                    <input type="text" name="department_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Department Head</label>
                    <select name="department_head_id" class="form-select">
                        <option value="">None</option>
                        <?php
                        $employees->data_seek(0);
                        while($emp = $employees->fetch_assoc()): ?>
                            <option value="<?= $emp['employee_id']; ?>"><?= htmlspecialchars($emp['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Parent Department</label>
                    <select name="parent_department_id" class="form-select">
                        <option value="">None</option>
                        <?php while($parent = $allDepartments->fetch_assoc()): ?>
                            <option value="<?= $parent['department_id']; ?>"><?= htmlspecialchars($parent['department_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_department" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="edit_department_id" id="edit_department_id">
                <div class="mb-3">
                    <label>Department Name</label>
                    <input type="text" name="edit_department_name" id="edit_department_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Department Head</label>
                    <select name="edit_department_head_id" id="edit_department_head_id" class="form-select">
                        <option value="">None</option>
                        <?php
                        $employees->data_seek(0);
                        while($emp = $employees->fetch_assoc()): ?>
                            <option value="<?= $emp['employee_id']; ?>"><?= htmlspecialchars($emp['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Parent Department</label>
                    <select name="edit_parent_department_id" id="edit_parent_department_id" class="form-select">
                        <option value="">None</option>
                        <?php
                        $allDepartmentsForEdit->data_seek(0);
                        while($parent = $allDepartmentsForEdit->fetch_assoc()): ?>
                            <option value="<?= $parent['department_id']; ?>"><?= htmlspecialchars($parent['department_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_department" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script>
    document.querySelectorAll('.edit-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_department_id').value = this.dataset.id;
            document.getElementById('edit_department_name').value = this.dataset.name;
            document.getElementById('edit_department_head_id').value = this.dataset.head || '';
            document.getElementById('edit_parent_department_id').value = this.dataset.parent || '';
        });
    });
</script>
</body>
</html>
