<?php
session_start();
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management - Procurement System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Warehouse Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                Add New Warehouse
            </button>
        </div>

        <div class="row">
            <!-- Warehouse List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <table id="warehousesTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Manager</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT w.*, e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as manager_name 
                                       FROM warehouses w 
                                       LEFT JOIN employees e ON w.manager_id = e.employee_id";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $row['warehouse_id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['location']; ?></td>
                                    <td><?php echo $row['capacity']; ?></td>
                                    <td><?php echo $row['manager_name']; ?></td>
                                    <td style = "width:150px;">
                                        <button class="btn btn-sm btn-info" onclick="editWarehouse(<?php echo $row['warehouse_id']; ?>)">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteWarehouse(<?php echo $row['warehouse_id']; ?>)">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Warehouse Statistics -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Warehouse Statistics</h5>
                        <?php
                        $stats = [
                            "SELECT COUNT(*) FROM warehouses" => "Total Warehouses",
                            "SELECT COUNT(DISTINCT warehouse_id) FROM inventory_items" => "Active Warehouses",
                            "SELECT COUNT(*) FROM inventory_items" => "Total Items Stored"
                        ];

                        foreach($stats as $sql => $label):
                            $result = $conn->query($sql);
                            $count = $result->fetch_row()[0];
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo $label; ?>:</span>
                            <strong><?php echo $count; ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Storage Utilization</h5>
                        <?php
                        $sql = "SELECT w.name, 
                               COUNT(i.item_id) as item_count,
                               w.capacity,
                               (COUNT(i.item_id) / w.capacity * 100) as utilization
                               FROM warehouses w
                               LEFT JOIN inventory_items i ON w.warehouse_id = i.warehouse_id
                               GROUP BY w.warehouse_id";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small><?php echo $row['name']; ?></small>
                                <small><?php echo round($row['utilization'], 1); ?>%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar <?php echo getUtilizationClass($row['utilization']); ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $row['utilization']; ?>%">
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Warehouse Modal -->
    <div class="modal fade" id="addWarehouseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="warehouseForm">
                        <input type="hidden" name="warehouse_id" id="warehouse_id">
                        <div class="mb-3">
                            <label class="form-label">Warehouse Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" name="capacity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Manager</label>
                            <select name="manager_id" class="form-select" required>
                                <option value="">Select Manager</option>
                                <?php
                                $sql = "SELECT employee_id, CONCAT(first_name, ' ', last_name) as name FROM employees";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['employee_id']; ?>">
                                    <?php echo $row['name']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submitWarehouse">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables@1.10.24/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#warehousesTable').DataTable();

            $('#submitWarehouse').click(function() {
                $.ajax({
                    url: 'ajax/save_warehouse.php',
                    method: 'POST',
                    data: $('#warehouseForm').serialize(),
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error saving warehouse: ' + response.message);
                        }
                    }
                });
            });
        });

        function editWarehouse(id) {
            $.ajax({
                url: 'ajax/get_warehouse.php',
                method: 'GET',
                data: { warehouse_id: id },
                success: function(response) {
                    if (response.success) {
                        $('#warehouse_id').val(response.data.warehouse_id);
                        $('input[name="name"]').val(response.data.name);
                        $('input[name="location"]').val(response.data.location);
                        $('input[name="capacity"]').val(response.data.capacity);
                        $('select[name="manager_id"]').val(response.data.manager_id);
                        $('#addWarehouseModal').modal('show');
                    }
                }
            });
        }

        function deleteWarehouse(id) {
            if (confirm('Are you sure you want to delete this warehouse?')) {
                $.ajax({
                    url: 'ajax/delete_warehouse.php',
                    method: 'POST',
                    data: { warehouse_id: id },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting warehouse: ' + response.message);
                        }
                    }
                });
            }
        }
    </script>

    <?php
    function getUtilizationClass($utilization) {
        if ($utilization >= 90) return 'bg-danger';
        if ($utilization >= 70) return 'bg-warning';
        return 'bg-success';
    }
    ?>
</body>
</html>