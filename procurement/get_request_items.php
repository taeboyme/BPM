<?php
// Include DB connection
include 'config/database.php'; // Make sure this path is correct

if (isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']); // Sanitize input

    // Prepare the SQL query to fetch the items
    $stmt = $conn->prepare("SELECT item_name, quantity, unit_price FROM request_items WHERE request_id = ?");
    $stmt->bind_param("i", $request_id); // Bind the request ID parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Store the items in an array
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    // Return the items as a JSON response
    echo json_encode($items);
} else {
    // If no request ID is passed, return an empty array
    echo json_encode([]);
}

$conn->close(); // Don't forget to close the DB connection
?>
