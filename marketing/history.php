<?php
include '../db.php'; // Include the database connection file

// Query to join history and staff tables using staff.id and history.actionBy
$query = "
SELECT h.poNumber, h.columnName, h.oldValue, h.newValue, h.actionBy, h.department, h.actionTime, 
       s.firstname, s.middlename, s.lastname
FROM history h
JOIN staff s ON h.actionBy = s.id
ORDER BY h.actionTime ASC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $historyData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $historyData = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .history-container {
            padding: 20px;
        }
        .history-entry {
            margin-bottom: 20px;
        }
        .timestamp {
            color: gray;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
      <h2><b>Marketing History</b></h2>
        <hr>
        <div class="history-container">
            <?php if (!empty($historyData)) : ?>
                <?php foreach ($historyData as $data) : ?>
                    <div class="history-entry">
                        <p>
                            <strong><?= htmlspecialchars($data['firstname'] . ' ' . $data['middlename'] . ' ' . $data['lastname']); ?></strong>, 
                            from the <strong><?= htmlspecialchars($data['department']); ?></strong> department,
                            updated the <strong><?= htmlspecialchars($data['columnName']); ?></strong> column 
                            for PO Number <strong><?= htmlspecialchars($data['poNumber']); ?></strong>.
                        </p>
                        <p>
                            The value was changed from <em>"<?= htmlspecialchars($data['oldValue']); ?>"</em> to 
                            <em>"<?= htmlspecialchars($data['newValue']); ?>"</em>.
                        </p>
                        <p class="timestamp">
                            This action was performed on <?= htmlspecialchars($data['actionTime']); ?>.
                        </p>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="alert alert-info text-center">
                    No history records found.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
