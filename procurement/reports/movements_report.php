<?php
// Validate date range
if (!isset($startDate) || !isset($endDate)) {
    echo "<div class='alert alert-warning'>Invalid date range selected.</div>";
    return;
}

// Fetch stock movement data
$sql = "SELECT m.movement_id, i.item_name, m.movement_type, m.quantity, m.movement_date, m.reference, w.name AS warehouse
        FROM inventory_movements m
        LEFT JOIN inventory_items i ON m.item_id = i.item_id
        LEFT JOIN warehouses w ON m.item_id = w.warehouse_id
        WHERE m.movement_date BETWEEN '$startDate' AND '$endDate'
        ORDER BY m.movement_date DESC";

$result = $conn->query($sql);
?>

<h4 class="mb-3">Stock Movements from <?php echo $startDate; ?> to <?php echo $endDate; ?></h4>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="stockMovementsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Warehouse</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($row['movement_date'])); ?></td>
                                <td><?= htmlspecialchars($row['item_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['movement_type'] === 'IN' ? 'success' : 'danger'; ?>">
                                        <?= $row['movement_type']; ?>
                                    </span>
                                </td>
                                <td><?= (int)$row['quantity']; ?></td>
                                <td><?= htmlspecialchars($row['warehouse']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">No stock movements found for the selected period.</div>
<?php endif; ?>
