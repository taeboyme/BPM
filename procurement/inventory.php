<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Inventory.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$inventory = new Inventory($conn);

// Handle Add Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $warehouse_id = $_POST['warehouse_id'];
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];

    $stmt = $conn->prepare("INSERT INTO inventory_items (warehouse_id, item_name, description, quantity, unit) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $warehouse_id, $item_name, $description, $quantity, $unit);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Stock Movement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_movement'])) {
    $item_id = $_POST['item_id'];
    $movement_type = $_POST['movement_type'];
    $quantity = $_POST['quantity'];
    $reference = $_POST['reference'];

    $conn->begin_transaction();

    try {
        // Insert into movement history
        $stmt = $conn->prepare("INSERT INTO inventory_movements (item_id, movement_type, quantity, reference, movement_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isis", $item_id, $movement_type, $quantity, $reference);
        $stmt->execute();
        $stmt->close();

        // Update quantity in inventory_items
        $quantityUpdate = $movement_type === 'IN' ? "+?" : "-?";
        $sql = "UPDATE inventory_items SET quantity = quantity $quantityUpdate WHERE item_id = ?";
        $stmt = $conn->prepare("UPDATE inventory_items SET quantity = quantity " . ($movement_type === 'IN' ? "+ ?" : "- ?") . " WHERE item_id = ?");
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Movement Error: " . $e->getMessage());
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Procurement System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#items">Inventory Items</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#movements">Movement History</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Inventory Items Tab -->
            <div class="tab-pane fade show active" id="items">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Inventory Items</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        Add New Item
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table id="itemsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Warehouse</th>
                                    <th>Item Name</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT i.*, w.name as warehouse_name 
                                       FROM inventory_items i 
                                       LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $row['item_id']; ?></td>
                                    <td><?php echo $row['warehouse_name']; ?></td>
                                    <td><?php echo $row['item_name']; ?></td>
                                    <td><?php echo $row['description']; ?></td>
                                    <td>
                                        <span class="<?php echo $row['quantity'] < 10 ? 'text-danger' : ''; ?>">
                                            <?php echo $row['quantity']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['unit']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="showMovementModal('IN', <?php echo $row['item_id']; ?>)">
                                            Stock In
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="showMovementModal('OUT', <?php echo $row['item_id']; ?>)">
                                            Stock Out
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Movement History Tab -->
            <div class="tab-pane fade" id="movements">
                <h3>Movement History</h3>
                <div class="card">
                    <div class="card-body">
                        <table id="movementsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, i.item_name 
                                       FROM inventory_movements m 
                                       LEFT JOIN inventory_items i ON m.item_id = i.item_id 
                                       ORDER BY m.movement_date DESC";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($row['movement_date'])); ?></td>
                                    <td><?php echo $row['item_name']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['movement_type'] == 'IN' ? 'success' : 'warning'; ?>">
                                            <?php echo $row['movement_type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo $row['reference']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addItemForm" method="POST" action="">
                        <input type="hidden" name="add_item" value="1">
                        <div class="mb-3">
                            <label class="form-label">Warehouse</label>
                            <select name="warehouse_id" class="form-select" required>
                                <?php
                                $sql = "SELECT * FROM warehouses";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['warehouse_id']; ?>">
                                    <?php echo $row['name']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Initial Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control" required>
                        </div>
                        <br/>
                        <div class="modal-footer" style = "width:498px; margin-left:-16px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Item</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Movement Modal -->
    <div class="modal fade" id="movementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Stock Movement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="movementForm" method="POST" action="">
                        <input type="hidden" name="record_movement" value="1">
                        <input type="hidden" name="item_id" id="movement_item_id">
                        <input type="hidden" name="movement_type" id="movement_type">
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control" readonly>
                        </div>
                        <br/>
                        <div class="modal-footer" style = "width:498px; margin-left:-16px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables@1.10.24/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function () {
        $('#itemsTable, #movementsTable').DataTable();
    });

    function showMovementModal(type, itemId) {
        const now = new Date();
        const timestamp = now.getFullYear().toString() +
        ('0' + (now.getMonth() + 1)).slice(-2) +
        ('0' + now.getDate()).slice(-2) + '-' +
        ('0' + now.getHours()).slice(-2) +
        ('0' + now.getMinutes()).slice(-2) +
        ('0' + now.getSeconds()).slice(-2);

        const reference = `MV-${timestamp}-${itemId}-${type.toUpperCase()}`;

        $('#movement_type').val(type);
        $('#movement_item_id').val(itemId);
        $('input[name="reference"]').val(reference);
        $('#movementModal').modal('show');
    }
</script>
</body>
</html>