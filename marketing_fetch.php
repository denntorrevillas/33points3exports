<?php
// Include database connection
include 'db.php'; // Make sure this is the correct path to your database connection file

// SQL query to fetch data from the marketing_department table
$sql = "SELECT poNumber, receivedOrder, businessAward, endorsedToGM, orderReceived, deadline, daysLeft FROM marketing_department";

// Execute the query
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    // Output the results as a table (or you can manipulate data further)
    while ($row = $result->fetch_assoc()) {
        echo "PO Number: " . htmlspecialchars($row['poNumber']) . "<br>";
        echo "Received Order: " . htmlspecialchars($row['receivedOrder']) . "<br>";
        echo "Business Award: " . htmlspecialchars($row['businessAward']) . "<br>";
        echo "Endorsed to GM: " . htmlspecialchars($row['endorsedToGM']) . "<br>";
        echo "Order Received: " . htmlspecialchars($row['orderReceived']) . "<br>";
        echo "Deadline: " . htmlspecialchars($row['deadline']) . "<br>";
        echo "Days Left: " . htmlspecialchars($row['daysLeft']) . "<br><br>";
    }
} else {
    echo "No records found.";
}

// Close the database connection
$conn->close();
?>
