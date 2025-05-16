<?php
// Include database connection
include '../db.php';

// Fetch data from the database
$query = "SELECT poNumber, buyer, orderDate, shipDate, DATEDIFF(shipDate, CURDATE()) AS daysLeft, overallStatus,leadTime FROM Orders";
$result = $conn->query($query);

// Initialize an empty array to store rows
$orders = [];

if ($result->num_rows > 0) {
    // Fetch all rows as an associative array
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Close the database connection
$conn->close();

// Return orders as a PHP variable for inclusion
return $orders;
?>
