<?php
class Movement {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMovements($item_id = null, $start_date = null, $end_date = null) {
    $sql = "SELECT 
                m.*, 
                i.item_name, 
                i.unit, 
                w.name AS warehouse_name
            FROM inventory_movements m
            JOIN inventory_items i ON m.item_id = i.item_id
            JOIN warehouses w ON i.warehouse_id = w.warehouse_id
            WHERE 1";

    $params = [];
    $types = '';

    // Filter by item_id
    if ($item_id) {
        $sql .= " AND m.item_id = ?";
        $params[] = $item_id;
        $types .= 'i';
    }

    // Filter by movement date
    if ($start_date && $end_date) {
        $sql .= " AND DATE(m.movement_date) BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= 'ss';
    }

    $sql .= " ORDER BY m.movement_date DESC";

    $stmt = $this->conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


    public function getRecentMovements($item_id, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT movement_date, movement_type, quantity, reference
            FROM inventory_movements
            WHERE item_id = ?
            ORDER BY movement_date DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $item_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getMonthlyStatistics($item_id) {
        $start_date = date('Y-m-01'); // First day of current month
        $end_date = date('Y-m-t');    // Last day of current month

        $sql = "SELECT 
                    SUM(CASE WHEN movement_type = 'IN' THEN quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN movement_type = 'OUT' THEN quantity ELSE 0 END) as total_out,
                    COUNT(*) as movement_count,
                    AVG(quantity) as avg_quantity
                FROM inventory_movements 
                WHERE item_id = ? 
                AND movement_date BETWEEN ? AND ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $item_id, $start_date, $end_date);
        $stmt->execute();

        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();

        // Format the results
        return [
            'total_in' => (int)$stats['total_in'],
            'total_out' => (int)$stats['total_out'],
            'movement_count' => (int)$stats['movement_count'],
            'avg_quantity' => round($stats['avg_quantity'], 2)
        ];
    }
}
?>
