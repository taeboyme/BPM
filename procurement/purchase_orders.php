<?php
session_start();
require_once 'config/database.php';
require_once 'classes/PurchaseOrder.php';
require_once 'classes/Supplier.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$supplier = new Supplier($conn);

$purchaseOrder = new PurchaseOrder($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_order') {
        $request_id = $_POST['request_id'];
        $supplier_name = $_POST['supplier_name'];
        $total_amount = $_POST['total_amount'];
        $order_date = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO purchase_orders (request_id, supplier_name, total_amount, order_date, status) VALUES (?, ?, ?, ?, 'PENDING')");
        $stmt->bind_param("isds", $request_id, $supplier_name, $total_amount, $order_date);
        $stmt->execute();
        header("Location: purchase_orders.php");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE purchase_orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        header("Location: purchase_orders.php?" . http_build_query($_GET));
        exit;
    }

// Initialize variables
$totalAmount = 0;

// Check if the request ID is set (after form submission or page reload)
if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Query to fetch request items
    $sql = "SELECT * FROM purchase_request_items WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Calculate the total amount
    while ($row = $result->fetch_assoc()) {
        $totalAmount += $row['quantity'] * $row['unit_price']; // Assuming fields: quantity, unit_price
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - Procurement System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Purchase Orders</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                Create New Order
            </button>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="PENDING">Pending</option>
                            <option value="APPROVED">Approved</option>
                            <option value="DELIVERED">Delivered</option>
                            <option value="CANCELLED">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="card">
            <div class="card-body">
                <table id="ordersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Request ID</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $where = "1=1";
                        if (isset($_GET['status']) && !empty($_GET['status'])) {
                            $status = $conn->real_escape_string($_GET['status']);
                            $where .= " AND status = '$status'";
                        }
                        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                            $date_from = $conn->real_escape_string($_GET['date_from']);
                            $where .= " AND order_date >= '$date_from'";
                        }
                        if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                            $date_to = $conn->real_escape_string($_GET['date_to']);
                            $where .= " AND order_date <= '$date_to'";
                        }

                        $sql = "SELECT * FROM purchase_orders WHERE $where ORDER BY order_date DESC";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['request_id']; ?></td>
                            <td><?php echo $row['supplier_name']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo getStatusColor($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-btn"
    data-bs-toggle="modal"
    data-bs-target="#viewOrderModal"
    data-order-id="<?php echo $row['order_id']; ?>"
    data-request-id="<?php echo $row['request_id']; ?>"
    data-supplier-name="<?php echo htmlspecialchars($row['supplier_name']); ?>"
    data-order-date="<?php echo $row['order_date']; ?>"
    data-total-amount="<?php echo $row['total_amount']; ?>"
    data-status="<?php echo $row['status']; ?>">
    View
</button>
                                <?php if ($row['status'] == 'PENDING'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <input type="hidden" name="status" value="APPROVED">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this order?')">Approve</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <input type="hidden" name="status" value="CANCELLED">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this order?')">Cancel</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Purchase Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_order">
                        <div class="mb-3">
                            <label class="form-label">Purchase Request</label>
                            <select id="requestSelect" name="request_id" class="form-select" required>
                                <option value="">Select Request</option>
                                <?php
                                $sql = "SELECT * FROM purchase_requests WHERE status = 'APPROVED'";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <option value="<?php echo $row['request_id']; ?>">
                                    Request #<?php echo $row['request_id']; ?> - <?php echo $row['purpose']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_name" class="form-select" required>
                                <option value="">Select Supplier</option>
                                    <?php
                                    // Fetch suppliers from the database
                                    $sql = "SELECT * FROM suppliers";  // Assuming the suppliers are stored in the 'suppliers' table
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                <option value="<?php echo $row['company_name']; ?>">
                                    <?php echo $row['company_name']; ?>
                                </option>
                                    <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="text" name="total_amount" class="form-control" step="0.01" required readonly id="totalAmount">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Order ID:</strong> <span id="viewOrderId"></span></p>
                <p><strong>Request ID:</strong> <span id="viewRequestId"></span></p>
                <p><strong>Supplier:</strong> <span id="viewSupplier"></span></p>
                <p><strong>Order Date:</strong> <span id="viewOrderDate"></span></p>
                <p><strong>Total Amount:</strong> $<span id="viewTotalAmount"></span></p>
                <p><strong>Status:</strong> <span id="viewStatus" class="badge"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables@1.10.24/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ordersTable').DataTable();
        });
    </script>
    <script>
$(document).ready(function() {
    $('#requestSelect').change(function() {
        const requestId = $(this).val();
        if (requestId) {
            $.ajax({
                url: 'get_total_amount.php',
                type: 'POST',
                data: { request_id: requestId },
                success: function(response) {
                    $('#totalAmount').val(response);
                },
                error: function() {
                    $('#totalAmount').val('0.00');
                }
            });
        } else {
            $('#totalAmount').val('');
        }
    });
});
</script>
<script>
    $(document).on('click', '.view-btn', function () {
        $('#viewOrderId').text($(this).data('order-id'));
        $('#viewRequestId').text($(this).data('request-id'));
        $('#viewSupplier').text($(this).data('supplier-name'));
        $('#viewOrderDate').text($(this).data('order-date'));
        $('#viewTotalAmount').text(parseFloat($(this).data('total-amount')).toFixed(2));

        const status = $(this).data('status');
        $('#viewStatus').text(status).removeClass().addClass('badge').addClass('bg-' + getStatusColor(status));
    });

    function getStatusColor(status) {
        switch (status) {
            case 'PENDING': return 'warning';
            case 'APPROVED': return 'success';
            case 'DELIVERED': return 'info';
            case 'CANCELLED': return 'danger';
            default: return 'secondary';
        }
    }
</script>



    <?php
    function getStatusColor($status) {
        switch ($status) {
            case 'PENDING':
                return 'warning';
            case 'APPROVED':
                return 'success';
            case 'DELIVERED':
                return 'info';
            case 'CANCELLED':
                return 'danger';
            default:
                return 'secondary';
        }
    }
    ?>
</body>
</html>