<?php
// Include your database connection
include '../db.php'; // adjust the path accordingly

// SQL query to select specified columns
$query = "SELECT poNumber, receivedOrder, businessAward, endorsedToGM, orderReceived, dateCompleted, completionSpan FROM marketinghistory";

$result = $conn->query($query);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

// Fetch all rows as associative arrays
$marketingData = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Marketing History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Marketing History Table</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Received Order</th>
                <th>Business Award</th>
                <th>Endorsed To GM</th>
                <th>Order Received</th>
                <th>Date Completed</th>
                <th>Completion Span</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($marketingData)): ?>
                <?php foreach ($marketingData as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['poNumber']); ?></td>
                        <td><?= htmlspecialchars($row['receivedOrder']); ?></td>
                        <td><?= htmlspecialchars($row['businessAward']); ?></td>
                        <td><?= htmlspecialchars($row['endorsedToGM']); ?></td>
                        <td><?= htmlspecialchars($row['orderReceived']); ?></td>
                        <td><?= htmlspecialchars($row['dateCompleted']); ?></td>
                        <td><?= htmlspecialchars($row['completionSpan']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No data found in marketing history.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
