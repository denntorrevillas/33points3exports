<?php
// Include database connection
include 'db.php'; // This includes the database connection code

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug POST data to ensure keys are available
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    // Validate required fields
    if (
        empty($_POST['poNumber']) || 
        empty($_POST['buyer']) || 
        empty($_POST['deliveryDays']) || 
        empty($_POST['deliveryDate'])
    ) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    // Get form data
    $poNumber = $_POST['poNumber'];
    $buyer = $_POST['buyer'];
    $deliveryDays = $_POST['deliveryDays'];
    $shipDate = $_POST['deliveryDate'];
    $orderDate = date('Y-m-d');
    $overallStatus = "Not Started";

    // SQL query to insert data into the Orders table
    $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, overallStatus) VALUES (?, ?, ?, ?, ?)";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameters to the SQL query
        $stmt->bind_param("sssss", $poNumber, $buyer, $orderDate, $shipDate, $overallStatus);

        // Execute the statement
        if ($stmt->execute()) {
            // If successful, redirect back to the page or show success
            echo json_encode(["success" => true, "message" => "Order added successfully!"]);
        } else {
            // If there's an error executing the statement
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // If preparing the SQL query fails
        echo json_encode(["success" => false, "message" => "Error preparing query: " . $conn->error]);
    }

    // Close the database connection
    $conn->close();
} else {
    // If the form submission method is not POST
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
