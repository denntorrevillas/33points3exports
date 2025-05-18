<?php
// Database connection (adjust with your own credentials)
include '../db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data for stacked bar chart (statuses for each step)
$sqlStatuses = "SELECT 
    SUM(supplierEvaluated = 'Not Started') AS supplierEvaluatedNotStarted,
    SUM(supplierEvaluated = 'In Progress') AS supplierEvaluatedInProgress,
    SUM(supplierEvaluated = 'Completed') AS supplierEvaluatedCompleted,
    SUM(supplierPOCreated = 'Not Started') AS supplierPOCreatedNotStarted,
    SUM(supplierPOCreated = 'In Progress') AS supplierPOCreatedInProgress,
    SUM(supplierPOCreated = 'Completed') AS supplierPOCreatedCompleted,
    SUM(gmApproved = 'Not Started') AS gmApprovedNotStarted,
    SUM(gmApproved = 'In Progress') AS gmApprovedInProgress,
    SUM(gmApproved = 'Completed') AS gmApprovedCompleted,
    SUM(supplierPOIssued = 'Not Started') AS supplierPOIssuedNotStarted,
    SUM(supplierPOIssued = 'In Progress') AS supplierPOIssuedInProgress,
    SUM(supplierPOIssued = 'Completed') AS supplierPOIssuedCompleted
FROM monitoring";
$resultStatuses = $conn->query($sqlStatuses);
$statusCounts = $resultStatuses->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Monitoring Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        /* h2 {
            text-align: center;
            color: #333;
            margin: 20px 0;
        } */
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: start;
        }
        .chart-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1 1 calc(33% - 40px);
            max-width: 350px;
        }
        canvas {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h2><b>Monitoring Dashboard</b></h2>
    <hr>

    <div class="chart-container">
        <!-- Stacked Bar Chart -->
        <div class="chart-card">
            <h2>Status by Step</h2>
            <canvas id="stackedBarChart"></canvas>
        </div>

        <!-- Pie Charts -->
        <div class="chart-card">
            <h2>Supplier Evaluated Status</h2>
            <canvas id="supplierEvaluatedPieChart"></canvas>
        </div>
        <div class="chart-card">
            <h2>Supplier PO Created Status</h2>
            <canvas id="supplierPOCreatedPieChart"></canvas>
        </div>
        <div class="chart-card">
            <h2>GM Approved Status</h2>
            <canvas id="gmApprovedPieChart"></canvas>
        </div>
        <div class="chart-card">
            <h2>Supplier PO Issued Status</h2>
            <canvas id="supplierPOIssuedPieChart"></canvas>
        </div>
    </div>

    <script>
        // Updated Colors
        const colors = {
            notStarted: '#d9534f', // Muted red
            inProgress: '#5bc0de', // Muted blue
            completed: '#5cb85c', // Muted green
        };

        // Stacked Bar Chart Data
        const stackedBarData = {
            labels: ['Supplier Evaluated', 'Supplier PO Created', 'GM Approved', 'Supplier PO Issued'],
            datasets: [
                {
                    label: 'Not Started',
                    data: [
                        <?php echo $statusCounts['supplierEvaluatedNotStarted']; ?>,
                        <?php echo $statusCounts['supplierPOCreatedNotStarted']; ?>,
                        <?php echo $statusCounts['gmApprovedNotStarted']; ?>,
                        <?php echo $statusCounts['supplierPOIssuedNotStarted']; ?>
                    ],
                    backgroundColor: colors.notStarted,
                },
                {
                    label: 'In Progress',
                    data: [
                        <?php echo $statusCounts['supplierEvaluatedInProgress']; ?>,
                        <?php echo $statusCounts['supplierPOCreatedInProgress']; ?>,
                        <?php echo $statusCounts['gmApprovedInProgress']; ?>,
                        <?php echo $statusCounts['supplierPOIssuedInProgress']; ?>
                    ],
                    backgroundColor: colors.inProgress,
                },
                {
                    label: 'Completed',
                    data: [
                        <?php echo $statusCounts['supplierEvaluatedCompleted']; ?>,
                        <?php echo $statusCounts['supplierPOCreatedCompleted']; ?>,
                        <?php echo $statusCounts['gmApprovedCompleted']; ?>,
                        <?php echo $statusCounts['supplierPOIssuedCompleted']; ?>
                    ],
                    backgroundColor: colors.completed,
                }
            ],
        };

        const stackedBarCtx = document.getElementById('stackedBarChart').getContext('2d');
        new Chart(stackedBarCtx, {
            type: 'bar',
            data: stackedBarData,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true },
                },
            },
        });

        // Pie Charts Data Function
        const pieChartData = (notStarted, inProgress, completed) => ({
            labels: ['Not Started', 'In Progress', 'Completed'],
            datasets: [
                {
                    data: [notStarted, inProgress, completed],
                    backgroundColor: [colors.notStarted, colors.inProgress, colors.completed],
                }
            ],
        });

        // Supplier Evaluated Pie Chart
        new Chart(document.getElementById('supplierEvaluatedPieChart').getContext('2d'), {
            type: 'pie',
            data: pieChartData(
                <?php echo $statusCounts['supplierEvaluatedNotStarted']; ?>,
                <?php echo $statusCounts['supplierEvaluatedInProgress']; ?>,
                <?php echo $statusCounts['supplierEvaluatedCompleted']; ?>
            ),
        });

        // Supplier PO Created Pie Chart
        new Chart(document.getElementById('supplierPOCreatedPieChart').getContext('2d'), {
            type: 'pie',
            data: pieChartData(
                <?php echo $statusCounts['supplierPOCreatedNotStarted']; ?>,
                <?php echo $statusCounts['supplierPOCreatedInProgress']; ?>,
                <?php echo $statusCounts['supplierPOCreatedCompleted']; ?>
            ),
        });

        // GM Approved Pie Chart
        new Chart(document.getElementById('gmApprovedPieChart').getContext('2d'), {
            type: 'pie',
            data: pieChartData(
                <?php echo $statusCounts['gmApprovedNotStarted']; ?>,
                <?php echo $statusCounts['gmApprovedInProgress']; ?>,
                <?php echo $statusCounts['gmApprovedCompleted']; ?>
            ),
        });

        // Supplier PO Issued Pie Chart
        new Chart(document.getElementById('supplierPOIssuedPieChart').getContext('2d'), {
            type: 'pie',
            data: pieChartData(
                <?php echo $statusCounts['supplierPOIssuedNotStarted']; ?>,
                <?php echo $statusCounts['supplierPOIssuedInProgress']; ?>,
                <?php echo $statusCounts['supplierPOIssuedCompleted']; ?>
            ),
        });
    </script>
</body>
</html>
