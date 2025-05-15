<?php
// Database connection (adjust with your own credentials)
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to count all statuses
$sql = "SELECT overallStatus, COUNT(*) as count FROM orders GROUP BY overallStatus";
$result = $conn->query($sql);

// Initialize empty arrays for labels and counts
$labels = [];
$counts = [];

// Fetch the results and populate arrays
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['overallStatus'];
    $counts[] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Responsive layout to fit charts in a row */
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
            padding: 20px;
        }
        canvas {
            max-width: 180px;
            max-height: 180px;
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h2>Multiple Chart Examples</h2>

    <div class="chart-container">
        <!-- Pie Chart -->
        <canvas id="pieChart" width="180" height="180"></canvas>

        <!-- Bar Chart -->
        <canvas id="barChart" width="180" height="180"></canvas>

        <!-- Line Chart -->
        <canvas id="lineChart" width="180" height="180"></canvas>

        <!-- Doughnut Chart -->
        <canvas id="doughnutChart" width="180" height="180"></canvas>
    </div>

    <script>
        // Get PHP arrays dynamically
        const labels = <?php echo json_encode($labels); ?>;
        const counts = <?php echo json_encode($counts); ?>;

        // Common chart configuration for all charts
        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Order Status',
                data: counts,
                backgroundColor: ['#ff9999', '#66b3ff', '#99ff99', '#ffcc66'],
                borderColor: ['#ff6666', '#3399ff', '#66ff66', '#ffb84d'],
                borderWidth: 1
            }]
        };

        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            }
        });

        // Line Chart
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Doughnut Chart
        const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            }
        });
    </script>
</body>
</html>
