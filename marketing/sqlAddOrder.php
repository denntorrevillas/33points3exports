<?php
include '../db.php';

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
    $leadTime = (int)$_POST['leadTime'];

    $orderDate = date('Y-m-d');
    $shipDate = date('Y-m-d', strtotime("+$leadTime days"));
    $daysLeft = $leadTime;
    $overallStatus = "Not Started";

    $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, daysLeft, leadTime, overallStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssiss", $poNumber, $buyer, $orderDate, $shipDate, $daysLeft, $leadTime, $overallStatus);

        if ($stmt->execute()) {
            // Redirect with success query string (optional)
            header("Location: dashboard.php?success=1");
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
