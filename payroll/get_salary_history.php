<?php
require_once('config/database.php');

if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];
    
    $query = "SELECT es.*, sc.component_name, 
              e.first_name, e.last_name
              FROM employee_salary es
              JOIN salary_components sc ON es.component_id = sc.component_id
              JOIN employees e ON es.employee_id = e.employee_id
              WHERE es.employee_id = ?
              ORDER BY es.effective_from DESC, sc.component_name";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employee = $result->fetch_assoc();
    ?>
    <h6><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?>'s Salary History</h6>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Component</th>
                <th>Amount</th>
                <th>Effective From</th>
                <th>Effective To</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $result->data_seek(0);
            while($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo $row['component_name']; ?></td>
                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['effective_from']; ?></td>
                <td><?php echo $row['effective_to'] ?? 'Current'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php
}
?>