<?php
require_once 'config/database.php';

if (isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $stmt = $conn->prepare("SELECT quantity, unit_price FROM request_items WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['quantity'] * $row['unit_price'];
    }

    echo number_format($total, 2, '.', '');
}
?>
