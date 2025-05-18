<?php
include '../db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Status counts for finishing, packed, inspected
$sqlStatusCounts = "
    SELECT 
        SUM(finishing = 'Not Started') AS finishingNotStarted,
        SUM(finishing = 'In Progress') AS finishingInProgress,
        SUM(finishing = 'Completed') AS finishingCompleted,
        SUM(packed = 'Not Started') AS packedNotStarted,
        SUM(packed = 'In Progress') AS packedInProgress,
        SUM(packed = 'Completed') AS packedCompleted,
        SUM(inspected = 'Not Started') AS inspectedNotStarted,
        SUM(inspected = 'In Progress') AS inspectedInProgress,
        SUM(inspected = 'Completed') AS inspectedCompleted
    FROM production";  // <-- replace production with actual table name
$statusResult = $conn->query($sqlStatusCounts);
$statusCounts = $statusResult->fetch_assoc();

// Fetch daysLeft and leadTime per poNumber
$sqlTime = "SELECT poNumber, daysLeft, leadTime FROM production ORDER BY poNumber";  // replace table name
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
<title>Production Dashboard</title>
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
    <h2><b>Production Dashboard</b></h2>
    <hr>
    <div class="chart-container">

        <!-- Stacked Bar Chart for statuses -->
        <div class="chart-card" style="flex:1 1 100%; max-width:900px;">
            <h3>Status by Step</h3>
            <canvas id="statusStackedBar"></canvas>
        </div>

        <!-- Bar Chart for daysLeft vs leadTime -->
        <div class="chart-card" style="flex:1 1 100%; max-width:900px;">
            <h3>Days Left vs Lead Time per PO Number</h3>
            <canvas id="daysLeadBar"></canvas>
        </div>

    </div>

<script>
    // Status stacked bar chart data
    const statusData = {
        labels: ['Finishing', 'Packed', 'Inspected'],
        datasets: [
            {
                label: 'Not Started',
                data: [
                    <?php echo $statusCounts['finishingNotStarted']; ?>,
                    <?php echo $statusCounts['packedNotStarted']; ?>,
                    <?php echo $statusCounts['inspectedNotStarted']; ?>
                ],
                backgroundColor: '#e74c3c' // red
            },
            {
                label: 'In Progress',
                data: [
                    <?php echo $statusCounts['finishingInProgress']; ?>,
                    <?php echo $statusCounts['packedInProgress']; ?>,
                    <?php echo $statusCounts['inspectedInProgress']; ?>
                ],
                backgroundColor: '#f1c40f' // yellow
            },
            {
                label: 'Completed',
                data: [
                    <?php echo $statusCounts['finishingCompleted']; ?>,
                    <?php echo $statusCounts['packedCompleted']; ?>,
                    <?php echo $statusCounts['inspectedCompleted']; ?>
                ],
                backgroundColor: '#2ecc71' // green
            }
        ]
    };

    const ctxStatus = document.getElementById('statusStackedBar').getContext('2d');
    new Chart(ctxStatus, {
        type: 'bar',
        data: statusData,
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Days Left vs Lead Time bar chart data
    const daysLeadCtx = document.getElementById('daysLeadBar').getContext('2d');
    new Chart(daysLeadCtx, {
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
