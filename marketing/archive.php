<?php
// Include your database connection
include '../db.php'; // adjust the path accordingly

// SQL query to select specified columns
$query = "SELECT 
    mh.poNumber,
    mh.receivedOrder,
    mh.businessAward,
    mh.endorsedToGM,
    mh.orderReceived,
    mh.dateCompleted,
    mh.completionSpan,
    mh.staff_ID,
    CONCAT(s.firstname, ' ', s.lastname) AS fullname
FROM 
    marketinghistory mh
JOIN 
    staff s
ON 
    mh.staff_ID = s.staff_ID;
";

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
<div class="container">
    <h2><b>Marketing History Table</b></h2>
    <hr>
    <table class="table">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Received Order</th>
                <th>Business Award</th>
                <th>Endorsed To GM</th>
                <th>Order Received</th>
                <th>Date Completed</th>
                <th>Completion Span</th>
                <th>Completed By</th>
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
                         <td><?= htmlspecialchars($row['fullname']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No data found in marketing history.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>



   
    </script>
</div>
</body>
</html>
