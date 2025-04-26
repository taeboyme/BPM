<?php
session_start();
require_once('config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Handle component addition/update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $query = "INSERT INTO salary_components (component_name, component_type, is_taxable) 
                     VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $is_taxable = isset($_POST['is_taxable']) ? 1 : 0;
            $stmt->bind_param("ssi", $_POST['component_name'], $_POST['component_type'], $is_taxable);
            $stmt->execute();
        } elseif ($_POST['action'] == 'update') {
            $query = "UPDATE salary_components 
                     SET component_name = ?, component_type = ?, is_taxable = ? 
                     WHERE component_id = ?";
            $stmt = $conn->prepare($query);
            $is_taxable = isset($_POST['is_taxable']) ? 1 : 0;
            $stmt->bind_param("ssii", $_POST['component_name'], $_POST['component_type'], $is_taxable, $_POST['component_id']);
            $stmt->execute();
        } elseif ($_POST['action'] == 'delete') {
            $query = "DELETE FROM salary_components WHERE component_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $_POST['component_id']);
            $stmt->execute();
        }
        header("Location: salary_components.php?success=1");
        exit();
    }
}

// Fetch all components
$query = "SELECT * FROM salary_components ORDER BY component_type, component_name";
$components = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Salary Components</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Salary Components</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#componentModal">
                <i class="fas fa-plus"></i> Add Component
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Changes saved successfully!</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table id="componentsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Component Name</th>
                            <th>Type</th>
                            <th>Taxable</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($component = $components->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $component['component_name']; ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $component['component_type'] == 'EARNING' ? 'success' : 
                                        ($component['component_type'] == 'DEDUCTION' ? 'danger' : 'info'); 
                                ?>">
                                    <?php echo $component['component_type']; ?>
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-<?php echo $component['is_taxable'] ? 'check text-success' : 'times text-danger'; ?>"></i>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editComponent(<?php echo htmlspecialchars(json_encode($component)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteComponent(<?php echo $component['component_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Component Modal -->
    <div class="modal fade" id="componentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="componentForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Salary Component</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="component_id" id="componentId">
                        
                        <div class="form-group">
                            <label>Component Name</label>
                            <input type="text" name="component_name" id="componentName" 
                                   class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Component Type</label>
                            <select name="component_type" id="componentType" class="form-control" required>
                                <option value="EARNING">Earning</option>
                                <option value="DEDUCTION">Deduction</option>
                                <option value="BENEFIT">Benefit</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_taxable" id="isTaxable" 
                                       class="custom-control-input">
                                <label class="custom-control-label" for="isTaxable">
                                    Is Taxable
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="component_id" id="deleteComponentId">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this salary component?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#componentsTable').DataTable();
    });

    function editComponent(component) {
        $('#formAction').val('update');
        $('#componentId').val(component.component_id);
        $('#componentName').val(component.component_name);
        $('#componentType').val(component.component_type);
        $('#isTaxable').prop('checked', component.is_taxable == 1);
        $('#componentModal').modal('show');
    }

    function deleteComponent(componentId) {
        $('#deleteComponentId').val(componentId);
        $('#deleteModal').modal('show');
    }

    $('#componentModal').on('hidden.bs.modal', function() {
        $('#formAction').val('add');
        $('#componentId').val('');
        $('#componentForm').trigger('reset');
    });
    </script>
</body>
</html>