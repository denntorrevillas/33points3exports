<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        empty($_POST['poNumber']) || 
        empty($_POST['leadTime']) || 
        empty($_POST['buyer'])
    ) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    $poNumber = $_POST['poNumber'];
    $buyer = $_POST['buyer'];
    $leadTime = $_POST['leadTime'];
    $orderDate = date('Y-m-d');

    // Calculate ship date from lead time
    $shipDate = date('Y-m-d', strtotime("+$leadTime days"));
    $overallStatus = "Not Started";

    $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, overallStatus, leadTime) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssss", $poNumber, $buyer, $orderDate, $shipDate, $overallStatus, $leadTime);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Order added successfully!"]);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Execution error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Query error: " . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
