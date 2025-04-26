<?php
class Report {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getStockSummary($warehouse_id = null) {
        $where = $warehouse_id ? "WHERE warehouse_id = ?" : "";
        $sql = "SELECT 
                COUNT(DISTINCT item_id) as total_items,
                SUM(quantity) as total_quantity
                FROM inventory_items " . $where;
                
        if ($warehouse_id) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $warehouse_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    
    public function getMovementSummary($start_date, $end_date, $warehouse_id = null) {
        $where = "WHERE movement_date BETWEEN ? AND ?";
        if ($warehouse_id) {
            $where .= " AND warehouse_id = ?";
        }
        
        $sql = "SELECT 
                SUM(CASE WHEN movement_type = 'IN' THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = 'OUT' THEN quantity ELSE 0 END) as total_out
                FROM inventory_movements " . $where;
                
        $stmt = $this->conn->prepare($sql);
        
        if ($warehouse_id) {
            $stmt->bind_param("sss", $start_date, $end_date, $warehouse_id);
        } else {
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getLowStockItems($warehouse_id = null, $threshold = 10) {
        $where = "WHERE i.quantity <= ?";
        if ($warehouse_id) {
            $where .= " AND i.warehouse_id = ?";
        }
        
        $sql = "SELECT i.*, w.name as warehouse_name 
                FROM inventory_items i
                LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id
                " . $where . "
                ORDER BY i.quantity ASC";
                
        $stmt = $this->conn->prepare($sql);
        
        if ($warehouse_id) {
            $stmt->bind_param("is", $threshold, $warehouse_id);
        } else {
            $stmt->bind_param("i", $threshold);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getMovementTrendData($start_date, $end_date, $warehouse_id = null) {
        $where = "WHERE movement_date BETWEEN ? AND ?";
        if ($warehouse_id) {
            $where .= " AND warehouse_id = ?";
        }
        
        $sql = "SELECT 
                DATE(movement_date) as date,
                SUM(CASE WHEN movement_type = 'IN' THEN quantity ELSE 0 END) as stock_in,
                SUM(CASE WHEN movement_type = 'OUT' THEN quantity ELSE 0 END) as stock_out
                FROM inventory_movements
                " . $where . "
                GROUP BY DATE(movement_date)
                ORDER BY date ASC";
                
        $stmt = $this->conn->prepare($sql);
        
        if ($warehouse_id) {
            $stmt->bind_param("sss", $start_date, $end_date, $warehouse_id);
        } else {
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format data for Chart.js
        $dates = [];
        $stock_in = [];
        $stock_out = [];
        
        foreach ($result as $row) {
            $dates[] = $row['date'];
            $stock_in[] = $row['stock_in'];
            $stock_out[] = $row['stock_out'];
        }
        
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Stock In',
                    'data' => $stock_in,
                    'borderColor' => '#28a745',
                    'fill' => false
                ],
                [
                    'label' => 'Stock Out',
                    'data' => $stock_out,
                    'borderColor' => '#dc3545',
                    'fill' => false
                ]
            ]
        ];
    }
    
    public function getWarehouseCapacityData($warehouse_id = null) {
        $where = $warehouse_id ? "WHERE w.warehouse_id = ?" : "";
        
        $sql = "SELECT 
                w.name,
                w.capacity,
                COALESCE(SUM(i.quantity), 0) as used_capacity
                FROM warehouses w
                LEFT JOIN inventory_items i ON w.warehouse_id = i.warehouse_id
                " . $where . "
                GROUP BY w.warehouse_id
                ORDER BY w.name ASC";
                
        if ($warehouse_id) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $warehouse_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }
        
        $warehouses = [];
        $usage = [];
        
        while ($row = $result->fetch_assoc()) {
            $warehouses[] = $row['name'];
            $usage[] = ($row['capacity'] > 0) ? 
                      round(($row['used_capacity'] / $row['capacity']) * 100, 2) : 
                      0;
        }
        
        return [
            'labels' => $warehouses,
            'datasets' => [
                [
                    'label' => 'Capacity Usage (%)',
                    'data' => $usage,
                    'backgroundColor' => '#007bff',
                    'borderColor' => '#0056b3',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    
    public function generatePDFReport($type, $params = []) {
        // Implement PDF generation logic here
        // You can use libraries like TCPDF or FPDF
    }
    
    public function generateExcelReport($type, $params = []) {
        // Implement Excel generation logic here
        // You can use libraries like PHPSpreadsheet
    }
    
    public function generateCSVReport($type, $params = []) {
        // Implement CSV generation logic here
    }
}
?>