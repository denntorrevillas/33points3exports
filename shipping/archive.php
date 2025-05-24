<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping History</title>
</head>
<body>
    <div class="container">
        <h2><b>Shipping History</b></h2>
        <hr>
        <div style="overflow-x:scroll;">
            <table class="table">
            <thead class="table-dark">
                <tr>
                    <th>PO Number</th>
                    <th>Pre-Loading</th>
                    <th>Loading</th>
                    <th>Transported</th>
                    <th>Delivered to Customer</th>
                    <th>Date Received</th>
                    <th>Deadline</th>
                    <th>Lead Time</th>
                    <th>Date Completed</th>
                    <th>Completion Span</th>
                    <th>Completed By</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Include database connection
                include '../db.php';

                // SQL query to fetch data
                $sql = "SELECT 
    sh.poNumber,
    sh.pre_loading,
    sh.loading,
    sh.transported,
    sh.delivered_to_customer,
    sh.dateReceived,
    sh.deadline,
    sh.dateCompleted,
    sh.daysLeft,
    sh.leadTime,
    sh.completionSpan,
    sh.staff_ID,
    CONCAT(s.firstname, ' ', s.lastname) AS fullname
FROM 
    shippinghistory sh
JOIN 
    staff s
ON 
    sh.staff_ID = s.staff_ID";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['poNumber']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pre_loading']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['loading']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['transported']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['delivered_to_customer']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dateReceived']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['deadline']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['leadTime']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dateCompleted']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['completionSpan']) . "</td>";
                           echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>No records found</td></tr>";
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