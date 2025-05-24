<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>All Orders</title>
</head>
<body>
<div class="container mt-5">
    <h2><b>All Orders Combined History</b></h2>
    <hr>
   <div style="overflow-y: auto;">
             <table class="table " style="width:100%">
        <thead class="table-dark">
            <tr>
                <th>PO Number</th>

                <th>Marketing Received Order</th>
                <th>Marketing Lead Time</th>
                <th>Marketing Date Completed</th>
                <th>Marketing Completion Span</th>

                <th>Accounting Date Received</th>
                <th>Accounting Lead Time</th>
                <th>Accounting Date Completed</th>
                <th>Accounting Completion Span</th>

                <th>Monitoring Date Received</th>
                <th>Monitoring Lead Time</th>
                <th>Monitoring Date Completed</th>
                <th>Monitoring Completion Span</th>

                <th>Production Date Received</th>
                <th>Production Lead Time</th>
                <th>Production Date Completed</th>
                <th>Production Completion Span</th>

                <th>Shipping Date Received</th>
                <th>Shipping Lead Time</th>
                <th>Shipping Date Completed</th>
                <th>Shipping Completion Span</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../db.php';

            $sql = "
                SELECT 
                    mh.poNumber,
                    mh.receivedOrder AS marketingReceivedOrder,
                    mh.leadTime AS marketingLeadTime,
                    mh.dateCompleted AS marketingDateCompleted,
                    mh.completionSpan AS marketingCompletionSpan,

                    ah.dateReceived AS accountingReceivedOrder,
                    ah.leadTime AS accountingLeadTime,
                    ah.dateCompleted AS accountingDateCompleted,
                    ah.completionSpan AS accountingCompletionSpan,

                    moh.dateReceived AS monitoringReceivedOrder,
                    moh.leadTime AS monitoringLeadTime,
                    moh.dateCompleted AS monitoringDateCompleted,
                    moh.completionSpan AS monitoringCompletionSpan,

                    ph.dateReceived AS productionReceivedOrder,
                    ph.leadTime AS productionLeadTime,
                    ph.dateCompleted AS productionDateCompleted,
                    ph.completionSpan AS productionCompletionSpan,

                    sh.dateReceived AS shippingReceivedOrder,
                    sh.leadTime AS shippingLeadTime,
                    sh.dateCompleted AS shippingDateCompleted,
                    sh.completionSpan AS shippingCompletionSpan
                FROM marketinghistory mh
                LEFT JOIN accountinghistory ah ON mh.poNumber = ah.poNumber
                LEFT JOIN monitoringhistory moh ON mh.poNumber = moh.poNumber
                LEFT JOIN productionhistory ph ON mh.poNumber = ph.poNumber
                LEFT JOIN shippinghistory sh ON mh.poNumber = sh.poNumber
            ";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['poNumber']) . "</td>";

                    echo "<td>" . htmlspecialchars($row['marketingReceivedOrder'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['marketingLeadTime'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['marketingDateCompleted'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['marketingCompletionSpan'] ?? '') . "</td>";

                    echo "<td>" . htmlspecialchars($row['accountingReceivedOrder'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['accountingLeadTime'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['accountingDateCompleted'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['accountingCompletionSpan'] ?? '') . "</td>";

                    echo "<td>" . htmlspecialchars($row['monitoringReceivedOrder'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['monitoringLeadTime'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['monitoringDateCompleted'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['monitoringCompletionSpan'] ?? '') . "</td>";

                    echo "<td>" . htmlspecialchars($row['productionReceivedOrder'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['productionLeadTime'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['productionDateCompleted'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['productionCompletionSpan'] ?? '') . "</td>";

                    echo "<td>" . htmlspecialchars($row['shippingReceivedOrder'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['shippingLeadTime'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['shippingDateCompleted'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['shippingCompletionSpan'] ?? '') . "</td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='20' class='text-center'>No records found</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
   </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
