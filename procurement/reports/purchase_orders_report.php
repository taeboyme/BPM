<?php
// Validate dates
if (!isset($startDate) || !isset($endDate)) {
    echo "<div class='alert alert-warning'>Invalid date range selected.</div>";
    return;
}

// Fetch purchase orders within the selected date range
$sql = "SELECT po.order_id, po.order_date, po.supplier_name AS supplier, po.total_amount, po.status 
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.order_id = s.supplier_id
        WHERE po.order_date BETWEEN '$startDate' AND '$endDate'
        ORDER BY po.order_date DESC";

$result = $conn->query($sql);
?>

<h4 class="mb-3">Purchase Orders from <?php echo $startDate; ?> to <?php echo $endDate; ?></h4>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="purchaseOrderTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['order_id']); ?></td>
                                <td><?= date('Y-m-d', strtotime($row['order_date'])); ?></td>
                                <td><?= htmlspecialchars($row['supplier']); ?></td>
                                <td>$<?= number_format($row['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?=
                                        match ($row['status']) {
                                            'Pending' => 'secondary',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'info'
                                        };
                                    ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">No purchase orders found in the selected date range.</div>
<?php endif; ?>
