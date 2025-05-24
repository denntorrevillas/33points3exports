<?php
// PHP Code: Fetch data from database
include '../db.php';

$sql = "SELECT poNumber, buyer, orderDate, shipDate, DATEDIFF(shipDate, CURDATE()) AS daysLeft, 
               DATEDIFF(shipDate, orderDate) AS leadTime, overallStatus FROM orders";
$result = $conn->query($sql);

$data = [];
$orderCountsByDate = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;

    $orderDate = $row['orderDate'];
    if (!isset($orderCountsByDate[$orderDate])) {
        $orderCountsByDate[$orderDate] = 0;
    }
    $orderCountsByDate[$orderDate]++;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .chart-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .chart-card h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        #calendar {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
    </style>
</head>
<body>
    <h2><b>Order Dashboard</b></h2>
    <hr>
    <div class="dashboard-container">
        <div class="chart-card">
            <h3>Overall Status Breakdown</h3>
            <canvas id="overallStatusChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Lead Time Distribution</h3>
            <canvas id="leadTimeChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Days Left Distribution</h3>
            <canvas id="daysLeftChart"></canvas>
        </div>
    </div>
    <div id="calendar"></div>

    <script>
        // Fetch data from PHP
        const orderData = <?php echo json_encode($data); ?>;
        const orderCountsByDate = <?php echo json_encode($orderCountsByDate); ?>;

        const statusCounts = { NotStarted: 0, InProgress: 0, Completed: 0 };
        const leadTimes = [];
        const daysLeft = [];

        orderData.forEach(order => {
            if (order.overallStatus === "Not Started") statusCounts.NotStarted++;
            if (order.overallStatus === "In Progress") statusCounts.InProgress++;
            if (order.overallStatus === "Completed") statusCounts.Completed++;

            leadTimes.push(order.leadTime);
            daysLeft.push(order.daysLeft);
        });

        // Chart 1: Overall Status Breakdown
        new Chart(document.getElementById('overallStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Not Started', 'In Progress', 'Completed'],
                datasets: [{
                    data: [statusCounts.NotStarted, statusCounts.InProgress, statusCounts.Completed],
                    backgroundColor: ['#ff6384', '#ffce56', '#36a2eb'],
                }]
            }
        });

        // Chart 2: Lead Time Distribution
        new Chart(document.getElementById('leadTimeChart'), {
            type: 'bar',
            data: {
                labels: orderData.map(order => order.poNumber),
                datasets: [{
                    label: 'Lead Time (days)',
                    data: leadTimes,
                    backgroundColor: '#36a2eb',
                }]
            }
        });

        // Chart 3: Days Left Distribution
        new Chart(document.getElementById('daysLeftChart'), {
            type: 'line',
            data: {
                labels: orderData.map(order => order.poNumber),
                datasets: [{
                    label: 'Days Left',
                    data: daysLeft,
                    borderColor: '#ff6384',
                    fill: false
                }]
            }
        });

        // FullCalendar Initialization
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            const events = Object.keys(orderCountsByDate).map(date => ({
                title: `${orderCountsByDate[date]} Orders`,
                start: date
            }));

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: events
            });

            calendar.render();
        });
    </script>
</body>
</html>
