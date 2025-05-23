<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production History</title>
</head>
<body>
    <div class="container">
       <h2><b>Production History</b></h2>
       <hr>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>PO Number</th>
                    <th>Finishing</th>
                    <th>Packed</th>
                    <th>Inspected</th>
                    <th>Date Received</th>
                    <th>Deadline</th>
                    <th>Lead Time</th>
                    <th>Date Completed</th>
                    <th>Completion Span</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Include database connection
                include '../db.php';

                // SQL query to fetch data
                $sql = "SELECT poNumber, finishing, packed, inspected, dateReceived, deadline, leadTime, dateCompleted, completionSpan FROM productionhistory";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['poNumber']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['finishing']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['packed']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['inspected']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dateReceived']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['deadline']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['leadTime']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dateCompleted']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['completionSpan']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No records found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
