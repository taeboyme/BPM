<?php
// Validate date range
if (!isset($startDate) || !isset($endDate)) {
    echo "<div class='alert alert-warning'>Invalid date range selected.</div>";
    return;
}

// Fetch inventory status data
$sql = "SELECT item_name, quantity, unit_price, (quantity * unit_price) AS total_value
        FROM request_items
        ORDER BY item_name ASC";

$result = $conn->query($sql);
?>

<h4 class="mb-3">Inventory Status Report</h4>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="inventoryStatusTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['item_name']); ?></td>
                                <td><?= (int)$row['quantity']; ?></td>
                                <td>$<?= number_format($row['unit_price'], 2); ?></td>
                                <td>$<?= number_format($row['total_value'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">No inventory records found.</div>
<?php endif; ?>
