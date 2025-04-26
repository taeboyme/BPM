<?php
require_once 'config/database.php';

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $project_id = $_GET['id'];

    // Prepare the DELETE query to remove the project
    $query = "DELETE FROM projects WHERE project_id = ?";
    $stmt = $conn->prepare($query);

    // Bind the project ID to the prepared statement
    $stmt->bind_param('i', $project_id);

    // Execute the query
    if ($stmt->execute()) {
        // If the deletion was successful, redirect back to the projects list with a success message
        echo "<script>alert('Successfully delete project.'); window.location.href = 'projects.php?success=1';</script>";
    } else {
        // If there was an error, redirect with an error message
        echo "<script>alert('Error deleting project.'); window.location.href = 'projects.php?error=1';</script>";
    }

    // Close the statement
    $stmt->close();
} else {
    // If no valid project ID is provided, redirect with an error message
    echo "<script>alert('Invalid project ID.'); window.location.href = 'projects.php?error=invalid';</script>";
}

exit();
?>
