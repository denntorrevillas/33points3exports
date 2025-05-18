<?php
// Database connection (adjust with your own credentials)
include '../db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pie Chart data: overallStatus counts
$sqlStatus = "SELECT overallStatus, COUNT(*) as count FROM orders GROUP BY overallStatus";
$resultStatus = $conn->query($sqlStatus);
$labels = [];
$counts = [];
while ($row = $resultStatus->fetch_assoc()) {
    $labels[] = $row['overallStatus'];
    $counts[] = $row['count'];
}

// Bar Chart data: PO No, Days Left, Lead Time
$sqlBar = "SELECT poNumber, daysLeft, leadTime FROM orders";
$resultBar = $conn->query($sqlBar);
$poNumbers = [];
$daysLeftArr = [];
$leadTimeArr = [];
while ($row = $resultBar->fetch_assoc()) {
    $poNumbers[] = $row['poNumber'];
    $daysLeftArr[] = (int)$row['daysLeft'];
    $leadTimeArr[] = (int)$row['leadTime'];
}

// Stacked Bar Chart data: Status count by Buyer
$sqlStacked = "
  SELECT buyer, overallStatus, COUNT(*) as count 
  FROM orders 
  GROUP BY buyer, overallStatus
";
$resultStacked = $conn->query($sqlStacked);

$buyers = [];
$statusSet = [];
while ($row = $resultStacked->fetch_assoc()) {
    if (!in_array($row['buyer'], $buyers)) {
        $buyers[] = $row['buyer'];
    }
    if (!in_array($row['overallStatus'], $statusSet)) {
        $statusSet[] = $row['overallStatus'];
    }
}
// Initialize matrix
$stackedData = [];
foreach ($statusSet as $status) {
    $stackedData[$status] = array_fill(0, count($buyers), 0);
}
$resultStacked->data_seek(0);
while ($row = $resultStacked->fetch_assoc()) {
    $buyerIndex = array_search($row['buyer'], $buyers);
    $stackedData[$row['overallStatus']][$buyerIndex] = (int)$row['count'];
}

// Horizontal Bar Chart data: Avg Lead Time by Buyer
$sqlAvgLead = "SELECT buyer, AVG(leadTime) as avgLead FROM orders GROUP BY buyer";
$resultAvgLead = $conn->query($sqlAvgLead);
$buyersAvg = [];
$avgLeadTimes = [];
while ($row = $resultAvgLead->fetch_assoc()) {
    $buyersAvg[] = $row['buyer'];
    $avgLeadTimes[] = round((float)$row['avgLead'], 1);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Multiple Charts</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Responsive layout to fit charts in a row */
    .chart-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 20px;
    }
    canvas {
        max-width: 400px;
        max-height: 400px;
        width: 100%;
        height: auto;
    }
</style>
</head>
<body>
<h2><b>Dashboard</b></h2>
<hr>

<div class="chart-container">
    <!-- Pie Chart -->
    <div>
        <h3>Overall Status Count</h3>
        <canvas id="pieChart" width="300" height="300"></canvas>
    </div>

    <!-- Bar Chart -->
    <div>
        <h3>Days Left vs Lead Time by PO No.</h3>
        <canvas id="barChart" width="400" height="300"></canvas>
    </div>

    <!-- Stacked Bar Chart -->
    <div>
        <h3>Status Count by Buyer</h3>
        <canvas id="stackedBarChart" width="400" height="300"></canvas>
    </div>

    <!-- Horizontal Bar Chart -->
    <div>
        <h3>Average Lead Time by Buyer</h3>
        <canvas id="avgLeadTimeChart" width="400" height="300"></canvas>
    </div>
</div>

<script>
    // Pie Chart data
    const pieLabels = <?php echo json_encode($labels); ?>;
    const pieCounts = <?php echo json_encode($counts); ?>;

    // Bar Chart data
    const poNumbers = <?php echo json_encode($poNumbers); ?>;
    const daysLeft = <?php echo json_encode($daysLeftArr); ?>;
    const leadTime = <?php echo json_encode($leadTimeArr); ?>;

    // Stacked Bar Chart data
    const buyers = <?php echo json_encode($buyers); ?>;
    const statuses = <?php echo json_encode($statusSet); ?>;
    const stackedData = <?php echo json_encode($stackedData); ?>;

    // Horizontal Bar Chart data
    const buyersAvg = <?php echo json_encode($buyersAvg); ?>;
    const avgLeadTimes = <?php echo json_encode($avgLeadTimes); ?>;

    // Pie Chart config
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieLabels,
            datasets: [{
                label: 'Order Status',
                data: pieCounts,
                backgroundColor: ['#ff9999', '#66b3ff', '#99ff99', '#ffcc66'],
                borderColor: ['#ff6666', '#3399ff', '#66ff66', '#ffb84d'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Bar Chart config (Days Left vs Lead Time)
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: poNumbers,
            datasets: [
                {
                    label: 'Days Left',
                    data: daysLeft,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                },
                {
                    label: 'Lead Time',
                    data: leadTime,
                    backgroundColor: 'rgba(255, 206, 86, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Days' } },
                x: { title: { display: true, text: 'PO No.' } }
            },
            plugins: { legend: { position: 'top' } }
        }
    });

    // Stacked Bar Chart config (Status by Buyer)
    const datasetsStacked = statuses.map((status, i) => ({
        label: status,
        data: stackedData[status],
        backgroundColor: `hsl(${i * 60}, 70%, 60%)`
    }));

    const stackedCtx = document.getElementById('stackedBarChart').getContext('2d');
    new Chart(stackedCtx, {
        type: 'bar',
        data: {
            labels: buyers,
            datasets: datasetsStacked
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            },
            plugins: { legend: { position: 'top' } }
        }
    });

    // Horizontal Bar Chart config (Avg Lead Time by Buyer)
    const avgLeadCtx = document.getElementById('avgLeadTimeChart').getContext('2d');
    new Chart(avgLeadCtx, {
        type: 'bar',
        data: {
            labels: buyersAvg,
            datasets: [{
                label: 'Avg Lead Time (Days)',
                data: avgLeadTimes,
                backgroundColor: 'rgba(153, 102, 255, 0.7)'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: { beginAtZero: true },
                y: { title: { display: true, text: 'Buyer' } }
            },
            plugins: { legend: { display: false } }
        }
    });

</script>
</body>
</html>
