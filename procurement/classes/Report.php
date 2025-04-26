<?php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getTotalOrders($startDate, $endDate) {
        $sql = "SELECT COUNT(*) as total FROM purchase_orders 
                WHERE order_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getTotalAmount($startDate, $endDate) {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total FROM purchase_orders 
                WHERE order_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getAverageOrderValue($startDate, $endDate) {
        $sql = "SELECT COALESCE(AVG(total_amount), 0) as average FROM purchase_orders 
                WHERE order_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['average'];
    }

    public function getPendingOrders($startDate, $endDate) {
        $sql = "SELECT COUNT(*) as pending FROM purchase_orders 
                WHERE status = 'Pending' AND order_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['pending'];
    }

    public function getOrdersByStatus($startDate, $endDate) {
        $sql = "SELECT status, COUNT(*) as count FROM purchase_orders 
                WHERE order_date BETWEEN ? AND ?
                GROUP BY status";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[$row['status']] = $row['count'];
        }
        return $data;
    }

    public function getMonthlyTrend($startDate, $endDate) {
        $sql = "SELECT 
                    DATE_FORMAT(order_date, '%Y-%m') as month,
                    COUNT(*) as order_count,
                    SUM(total_amount) as total_amount
                FROM purchase_orders 
                WHERE order_date BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                ORDER BY month";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = ['labels' => [], 'orders' => [], 'amounts' => []];
        
        while ($row = $result->fetch_assoc()) {
            $data['labels'][] = date('M Y', strtotime($row['month'] . '-01'));
            $data['orders'][] = $row['order_count'];
            $data['amounts'][] = $row['total_amount'];
        }
        
        return $data;
    }

    public function getOrders($startDate, $endDate) {
        $sql = "SELECT po.*, s.company_name 
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_name = s.company_name
                WHERE po.order_date BETWEEN ? AND ?
                ORDER BY po.order_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getTopSuppliers($startDate, $endDate, $limit = 5) {
        $sql = "SELECT 
                    s.company_name,
                    COUNT(*) as order_count,
                    SUM(po.total_amount) as total_amount
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_name = s.company_name
                WHERE po.order_date BETWEEN ? AND ?
                GROUP BY s.supplier_id
                ORDER BY total_amount DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $startDate, $endDate, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function exportToCSV($startDate, $endDate) {
        $orders = $this->getOrders($startDate, $endDate);
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Order ID', 'Date', 'Supplier', 'Amount', 'Status']);
        
        while ($row = $orders->fetch_assoc()) {
            fputcsv($output, [
                $row['order_id'],
                $row['order_date'],
                $row['supplier_name'],
                $row['total_amount'],
                $row['status']
            ]);
        }
        
        fclose($output);
    }
}
?>