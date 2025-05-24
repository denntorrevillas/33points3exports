<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting History</title>
</head>
<body>
    <div class="container">
       <h2><b>Accounting History</b></h2>
       <hr>
        <table class="table">
            <thead class="table-dark">
                <tr>
                    <th>PO Number</th>
                    <th>Received Copy</th>
                    <th>Payment Received</th>
                    <th>Date Received</th>
                    <th>Deadline</th>
                    <th>Lead Time</th>
                    <th>Date Completed</th>
                    <th>Completion Span</th>
                    <th>Completed by</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Database connection
                include '../db.php';

               

                // SQL query to fetch data
                $sql = "SELECT 
    ah.poNumber,
    ah.receivedCopy,
    ah.paymentReceived,
    ah.dateReceived,
    ah.deadline,
    ah.daysLeft,
    ah.leadTime,
    ah.dateCompleted,
    ah.completionSpan,
    ah.staff_ID,
    CONCAT(s.firstname, ' ', s.lastname) AS fullname
FROM 
    accountinghistory ah
JOIN 
    staff s
ON 
    ah.staff_ID = s.staff_ID;
";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['poNumber']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['receivedCopy']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['paymentReceived']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dateReceived']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['deadline']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['leadTime']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dateCompleted']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['completionSpan']) . "</td>";
                         echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No records found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
