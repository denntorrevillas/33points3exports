<?php
include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch status counts for shipping steps
$sqlStatusCounts = "
    SELECT 
        SUM(pre_loading = 'Not Started') AS preLoadingNotStarted,
        SUM(pre_loading = 'In Progress') AS preLoadingInProgress,
        SUM(pre_loading = 'Completed') AS preLoadingCompleted,
        SUM(loading = 'Not Started') AS loadingNotStarted,
        SUM(loading = 'In Progress') AS loadingInProgress,
        SUM(loading = 'Completed') AS loadingCompleted,
        SUM(transported = 'Not Started') AS transportedNotStarted,
        SUM(transported = 'In Progress') AS transportedInProgress,
        SUM(transported = 'Completed') AS transportedCompleted,
        SUM(delivered_to_customer = 'Not Started') AS deliveredNotStarted,
        SUM(delivered_to_customer = 'In Progress') AS deliveredInProgress,
        SUM(delivered_to_customer = 'Completed') AS deliveredCompleted
    FROM shipping";  // Replace 'shipping' with your actual table name if different
$statusResult = $conn->query($sqlStatusCounts);
$statusCounts = $statusResult->fetch_assoc();

// Fetch daysLeft and leadTime per poNumber
$sqlTime = "SELECT poNumber, daysLeft, leadTime FROM shipping ORDER BY poNumber"; // Replace if needed
$timeResult = $conn->query($sqlTime);

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
<title>Shipping Dashboard</title>
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
    <h2><b>Shipping Dashboard</b></h2>
    <hr>
    <div class="chart-container">

        <!-- Stacked Bar Chart for Shipping Statuses -->
        <div class="chart-card" style="flex:1 1 100%; max-width:900px;">
            <h3>Status by Shipping Step</h3>
            <canvas id="shippingStatusStackedBar"></canvas>
        </div>

        <!-- Bar Chart for daysLeft vs leadTime -->
        <div class="chart-card" style="flex:1 1 100%; max-width:900px;">
            <h3>Days Left vs Lead Time per PO Number</h3>
            <canvas id="shippingDaysLeadBar"></canvas>
        </div>

    </div>

<script>
    // Shipping statuses stacked bar data
    const shippingStatusData = {
        labels: ['Pre Loading', 'Loading', 'Transported', 'Delivered to Customer'],
        datasets: [
            {
                label: 'Not Started',
                data: [
                    <?php echo $statusCounts['preLoadingNotStarted']; ?>,
                    <?php echo $statusCounts['loadingNotStarted']; ?>,
                    <?php echo $statusCounts['transportedNotStarted']; ?>,
                    <?php echo $statusCounts['deliveredNotStarted']; ?>
                ],
                backgroundColor: '#e74c3c' // red
            },
            {
                label: 'In Progress',
                data: [
                    <?php echo $statusCounts['preLoadingInProgress']; ?>,
                    <?php echo $statusCounts['loadingInProgress']; ?>,
                    <?php echo $statusCounts['transportedInProgress']; ?>,
                    <?php echo $statusCounts['deliveredInProgress']; ?>
                ],
                backgroundColor: '#f1c40f' // yellow
            },
            {
                label: 'Completed',
                data: [
                    <?php echo $statusCounts['preLoadingCompleted']; ?>,
                    <?php echo $statusCounts['loadingCompleted']; ?>,
                    <?php echo $statusCounts['transportedCompleted']; ?>,
                    <?php echo $statusCounts['deliveredCompleted']; ?>
                ],
                backgroundColor: '#2ecc71' // green
            }
        ]
    };

    const shippingStatusCtx = document.getElementById('shippingStatusStackedBar').getContext('2d');
    new Chart(shippingStatusCtx, {
        type: 'bar',
        data: shippingStatusData,
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Days Left vs Lead Time bar chart data for shipping
    const shippingDaysLeadCtx = document.getElementById('shippingDaysLeadBar').getContext('2d');
    new Chart(shippingDaysLeadCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($poNumbers); ?>,
            datasets: [
                {
                    label: 'Days Left',
                    data: <?php echo json_encode($daysLeft); ?>,
                    backgroundColor: '#3498db' // blue
                },
                {
                    label: 'Lead Time',
                    data: <?php echo json_encode($leadTime); ?>,
                    backgroundColor: '#9b59b6' // purple
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Days' }
                },
                x: {
                    title: { display: true, text: 'PO Number' }
                }
            },
            plugins: { legend: { position: 'top' } }
        }
    });
</script>
</body>
</html>
