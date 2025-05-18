<?php
// Database connection (adjust credentials)
include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch status counts for receivedCopy and paymentReceived
$sqlStatuses = "
    SELECT 
        SUM(receivedCopy = 'Not Started') AS receivedCopyNotStarted,
        SUM(receivedCopy = 'In Progress') AS receivedCopyInProgress,
        SUM(receivedCopy = 'Completed') AS receivedCopyCompleted,
        SUM(paymentReceived = 'Not Started') AS paymentReceivedNotStarted,
        SUM(paymentReceived = 'In Progress') AS paymentReceivedInProgress,
        SUM(paymentReceived = 'Completed') AS paymentReceivedCompleted
    FROM accounting";
$statusResult = $conn->query($sqlStatuses);
$statusCounts = $statusResult->fetch_assoc();

// Fetch daysLeft and leadTime per poNumber
$sqlTimeData = "SELECT poNumber, daysLeft, leadTime FROM accounting ORDER BY poNumber";
$timeResult = $conn->query($sqlTimeData);

$poNumbers = [];
$daysLeft = [];
$leadTime = [];

while ($row = $timeResult->fetch_assoc()) {
    $poNumbers[] = $row['poNumber'];
    $daysLeft[] = (int)$row['daysLeft'];
    $leadTime[] = (int)$row['leadTime'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Accounting Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background-color: #f9f9f9;
        }
     
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }
        .chart-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.12);
            padding: 20px;
            max-width: 360px;
            flex: 1 1 320px;
        }
        canvas {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h2><b>Accounting Department</b></h2>
    <hr>
    <div class="chart-container">
        <div class="chart-card">
            <h3>Received Copy Status</h3>
            <canvas id="receivedCopyPie"></canvas>
        </div>
        <div class="chart-card">
            <h3>Payment Received Status</h3>
            <canvas id="paymentReceivedPie"></canvas>
        </div>
        <div class="chart-card" style="flex: 1 1 100%; max-width: 900px;">
            <h3>Days Left vs Lead Time per PO Number</h3>
            <canvas id="daysLeadBar"></canvas>
        </div>
    </div>

    <script>
        // Pie chart data helper
        function pieData(notStarted, inProgress, completed) {
            return {
                labels: ['Not Started', 'In Progress', 'Completed'],
                datasets: [{
                    data: [notStarted, inProgress, completed],
                    backgroundColor: ['#e74c3c', '#f1c40f', '#2ecc71'],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 20
                }]
            };
        }

        // Received Copy Pie Chart
        const receivedCopyCtx = document.getElementById('receivedCopyPie').getContext('2d');
        new Chart(receivedCopyCtx, {
            type: 'pie',
            data: pieData(
                <?php echo $statusCounts['receivedCopyNotStarted']; ?>,
                <?php echo $statusCounts['receivedCopyInProgress']; ?>,
                <?php echo $statusCounts['receivedCopyCompleted']; ?>
            ),
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: false },
                }
            }
        });

        // Payment Received Pie Chart
        const paymentReceivedCtx = document.getElementById('paymentReceivedPie').getContext('2d');
        new Chart(paymentReceivedCtx, {
            type: 'pie',
            data: pieData(
                <?php echo $statusCounts['paymentReceivedNotStarted']; ?>,
                <?php echo $statusCounts['paymentReceivedInProgress']; ?>,
                <?php echo $statusCounts['paymentReceivedCompleted']; ?>
            ),
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: false },
                }
            }
        });

        // Days Left vs Lead Time Bar Chart
        const daysLeadCtx = document.getElementById('daysLeadBar').getContext('2d');
        new Chart(daysLeadCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($poNumbers); ?>,
                datasets: [
                    {
                        label: 'Days Left',
                        data: <?php echo json_encode($daysLeft); ?>,
                        backgroundColor: '#3498db'
                    },
                    {
                        label: 'Lead Time',
                        data: <?php echo json_encode($leadTime); ?>,
                        backgroundColor: '#9b59b6'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                stacked: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Days' }
                    },
                    x: {
                        title: { display: true, text: 'PO Number' }
                    }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    </script>
</body>
</html>
