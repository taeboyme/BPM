<?php
session_start();
require_once 'config/database.php';
require_once 'classes/PurchaseRequest.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$purchaseRequest = new PurchaseRequest($conn);

if (isset($_GET['request_id'])) {
    $requestId = intval($_GET['request_id']);
    $sql = "SELECT item_name, quantity, unit_price FROM request_items WHERE request_id = $requestId";
    $result = $conn->query($sql);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
}

// Handle request status updates
if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $requestId = $_GET['request_id'];
    if ($_GET['action'] == 'approve') {
        $purchaseRequest->updateStatus($requestId, 'APPROVED');
        header('Location: purchase_requests.php');
        exit();
    } elseif ($_GET['action'] == 'reject') {
        $purchaseRequest->updateStatus($requestId, 'REJECTED');
        header('Location: purchase_requests.php');
        exit();
    }
}
// Handle create request form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $employeeId = $_POST['employee_id'];
    $departmentId = $_POST['department_id'];
    $purpose = $_POST['purpose'];
    $items = $_POST['items'];

    // Insert request into the database
    $success = $purchaseRequest->createRequest($employeeId, $departmentId, $purpose);

    if ($success) {
        // Get the last inserted request ID (assuming it was just created)
        $requestId = $conn->insert_id;

        // Insert each item into the request_items table
        foreach ($items as $item) {
            $itemName = $item['name'];
            $quantity = $item['quantity'];
            $unitPrice = $item['unit_price'];
            $purchaseRequest->addRequestItem($requestId, $itemName, $quantity, $unitPrice);
        }

        header('Location: purchase_requests.php');
        exit();
    } else {
        // Handle the error in case the request creation failed
        echo "Error: Unable to create the request.";
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Requests - Procurement System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Purchase Requests</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                Create New Request
            </button>
        </div>

        <!-- Request List -->
        <div class="card">
            <div class="card-body">
                <table id="requestsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT pr.*, 
                               CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                               d.department_name
                               FROM purchase_requests pr
                               LEFT JOIN employees e ON pr.employee_id = e.employee_id
                               LEFT JOIN departments d ON pr.department_id = d.department_id
                               ORDER BY pr.request_date DESC";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['request_id']; ?></td>
                            <td><?php echo $row['employee_name']; ?></td>
                            <td><?php echo $row['department_name']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['request_date'])); ?></td>
                            <td><?php echo $row['purpose']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo getStatusColor($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm viewRequestBtn"
                                        data-id="<?= $row['request_id']; ?>"
                                        data-date="<?= $row['request_date']; ?>"
                                        data-employee="<?= $row['employee_name']; ?>"
                                        data-department="<?= $row['department_name']; ?>"
                                        data-status="<?= $row['status']; ?>"
                                        data-purpose="<?= htmlspecialchars($row['purpose']);?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewRequestModal">
                                        View
                                        </button>
                                <?php if ($row['status'] == 'PENDING'): ?>
                                <a href="?action=approve&request_id=<?php echo $row['request_id']; ?>" class="btn btn-sm btn-success">
                                    Approve
                                </a>
                                <a href="?action=reject&request_id=<?php echo $row['request_id']; ?>" class="btn btn-sm btn-danger">
                                    Reject
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Request Modal -->
    <div class="modal fade" id="createRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Purchase Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="purchase_requests.php" method="POST" id="requestForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" class="form-select" required>
                                    <?php
                                    $sql = "SELECT * FROM employees";
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $row['employee_id']; ?>">
                                        <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select" required>
                                    <?php
                                    $sql = "SELECT * FROM departments";
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $row['department_id']; ?>">
                                        <?php echo $row['department_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose</label>
                            <textarea name="purpose" class="form-control" rows="3" required></textarea>
                        </div>
                        <div id="itemsContainer">
                            <h6>Request Items</h6>
                            <div class="request-item mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" name="items[0][name]" class="form-control" placeholder="Item Name" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[0][unit_price]" class="form-control" placeholder="Unit Price" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="addItemBtn">Add Another Item</button>
                        <div class="modal-footer" style = "margin-top:10px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- View Purchase Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Purchase Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Request ID</label>
                        <div id="viewRequestId" class="form-control-plaintext"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date</label>
                        <div id="viewDate" class="form-control-plaintext"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Employee</label>
                        <div id="viewEmployee" class="form-control-plaintext"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Department</label>
                        <div id="viewDepartment" class="form-control-plaintext"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <div><span id="viewStatus" class="badge"></span></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Purpose</label>
                    <div id="viewPurpose" class="form-control-plaintext"></div>
                </div>

                <hr>
                <div class="mb-3">
                    <h6 class="fw-bold">Requested Items</h6>
                    <br/>
                    <div id="viewItemsList">
                        <?php if ($items_result->num_rows > 0): ?>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <label><?= $item['item_name']; ?></label>
                            </div>
                            <div class="col-md-4">
                                <label><?= $item['quantity']; ?></label>
                            </div>
                            <div class="col-md-4">
                                <label><?= $item['unit_price']; ?></label>
                            </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p><em>No items found for this request.</em></p>
                <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCount = 1;
        document.getElementById('addItemBtn').addEventListener('click', function() {
            const newItem = `
                <div class="request-item mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="items[${itemCount}][name]" class="form-control" placeholder="Item Name" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[${itemCount}][quantity]" class="form-control" placeholder="Quantity" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[${itemCount}][unit_price]" class="form-control" placeholder="Unit Price" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-item">Remove</button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', newItem);
            itemCount++;
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-item')) {
                event.target.closest('.request-item').remove();
            }
        });
    </script>
    <script>
document.querySelectorAll('.viewRequestBtn').forEach(button => {
    button.addEventListener('click', function () {
        const requestId = this.dataset.id;

        document.getElementById('viewRequestId').textContent = requestId;
        document.getElementById('viewDate').textContent = this.dataset.date;
        document.getElementById('viewEmployee').textContent = this.dataset.employee;
        document.getElementById('viewDepartment').textContent = this.dataset.department;
        document.getElementById('viewStatus').textContent = this.dataset.status;
        document.getElementById('viewPurpose').textContent = this.dataset.purpose;

        const itemsContainer = document.getElementById('viewItemsList');
        itemsContainer.innerHTML = '<p>Loading items...</p>';

        fetch(`get_request_items.php?request_id=${requestId}`)
            .then(response => response.json())
            .then(items => {
                if (items.length === 0) {
                    itemsContainer.innerHTML = "<em>No items found for this request.</em>";
                    return;
                }

                itemsContainer.innerHTML = '';
                items.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'row mb-2';
                    row.innerHTML = `
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Item Name</label>
                            <label class="form-control-plaintext">${item.item_name}</label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Quantity</label>
                            <label class="form-control-plaintext">${item.quantity}</label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unit Price</label>
                            <label class="form-control-plaintext">${item.unit_price}</label>
                        </div>
                    `;
                    itemsContainer.appendChild(row);
                });
            });
            const status = $(this).data('status');
        $('#viewStatus').text(status).removeClass().addClass('badge').addClass('bg-' + getStatusColor(status));
        
        function getStatusColor(status) {
        switch (status) {
            case 'PENDING': return 'warning';
            case 'APPROVED': return 'success';
            case 'DELIVERED': return 'info';
            case 'CANCELLED': return 'danger';
            default: return 'secondary';
        }
    }
    });
});
</script>



    <?php
    function getStatusColor($status) {
        switch ($status) {
            case 'PENDING':
                return 'warning';
            case 'APPROVED':
                return 'success';
            case 'REJECTED':
                return 'danger';
            default:
                return 'secondary';
        }
    }
    ?>
</body>
</html>
