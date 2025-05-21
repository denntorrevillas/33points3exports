<?php
// session_start();
include '../db.php'; // Assuming the database connection is in db.php

// Query to fetch data from the marketing table
$query = "
SELECT * FROM marketing
WHERE NOT (
    TRIM(LOWER(receivedOrder)) = 'Completed'
    AND TRIM(LOWER(businessAward)) = 'Completed'
    AND TRIM(LOWER(endorsedToGM)) = 'Completed'
);
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $marketingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $marketingData = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $receivedOrderNew = $_POST['receivedOrder'] ?? 'Not Started';
    $businessAwardNew = $_POST['businessAward'] ?? 'Not Started';
    $endorsedToGMNew = $_POST['endorsedToGM'] ?? 'Not Started';
    $leadTimeNew = isset($_POST['leadTime']) && $_POST['leadTime'] !== '' ? $_POST['leadTime'] : NULL;

   // Fetch old values for comparison
$fetchOld = $conn->prepare("SELECT receivedOrder, businessAward, endorsedToGM, leadTime FROM marketing WHERE poNumber = ?");
$fetchOld->bind_param("s", $poNumber);
$fetchOld->execute();
$fetchOld->store_result();

// Initialize variables
$receivedOrderOld = $businessAwardOld = $endorsedToGMOld = $leadTimeOld = null;

$fetchOld->bind_result($receivedOrderOld, $businessAwardOld, $endorsedToGMOld, $leadTimeOld);
$fetchOld->fetch();
$fetchOld->close();

// Update marketing table
$updateQuery = "UPDATE marketing SET receivedOrder = ?, businessAward = ?, endorsedToGM = ?, leadTime = ? WHERE poNumber = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("sssss", $receivedOrderNew, $businessAwardNew, $endorsedToGMNew, $leadTimeNew, $poNumber);

if ($stmt->execute()) {
    // Prepare to insert into history table
    
   $staff_id = $_SESSION['staff_id'] ?? 'Unknown';
    $department = 'marketing';

    $historyInsert = $conn->prepare("INSERT INTO history (poNumber, columnName, oldValue, newValue, actionBy, department) VALUES (?, ?, ?, ?, ?, ?)");

    // Check each changed column and insert into history
    if ($receivedOrderOld !== $receivedOrderNew) {
        $columnName = 'receivedOrder';
        $historyInsert->bind_param("ssssss", $poNumber, $columnName, $receivedOrderOld, $receivedOrderNew, $staff_id, $department);
        $historyInsert->execute();
    }
    if ($businessAwardOld !== $businessAwardNew) {
        $columnName = 'businessAward';
        $historyInsert->bind_param("ssssss", $poNumber, $columnName, $businessAwardOld, $businessAwardNew, $staff_id, $department);
        $historyInsert->execute();
    }
    if ($endorsedToGMOld !== $endorsedToGMNew) {
        $columnName = 'endorsedToGM';
        $historyInsert->bind_param("ssssss", $poNumber, $columnName, $endorsedToGMOld, $endorsedToGMNew, $staff_id, $department);
        $historyInsert->execute();
    }
    if ((string)$leadTimeOld !== (string)$leadTimeNew) {
        $columnName = 'leadTime';
        $oldVal = $leadTimeOld === null ? 'NULL' : $leadTimeOld;
        $newVal = $leadTimeNew === null ? 'NULL' : $leadTimeNew;
        $historyInsert->bind_param("ssssss", $poNumber, $columnName, $oldVal, $newVal, $staff_id, $department);
        $historyInsert->execute();
    }

    $historyInsert->close();

    echo "<script>alert('Record updated successfully!');</script>";
} else {
    echo "<script>alert('Error updating record!');</script>";
}

$stmt->close();

}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Marketing Department</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
</head>
<body>
    <h2><b>Marketing Department</b></h2>
    <hr />

    <div class="table-div" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>PO No.</th>
                    <th>Received Order</th>
                    <th>Business Award</th>
                    <th>Endorsed to GM</th>
                    <th>Order Received</th>
                    <th>Deadline</th>
                    <th>Days Left</th>
                    <th>Lead Time</th>
                   
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($marketingData)) : ?>
                    <?php foreach ($marketingData as $data) : ?>
                        <tr>
                            <td><?= htmlspecialchars($data['poNumber']); ?></td>
                            <td><?= htmlspecialchars($data['receivedOrder']); ?></td>
                            <td><?= htmlspecialchars($data['businessAward']); ?></td>
                            <td><?= htmlspecialchars($data['endorsedToGM']); ?></td>
                            <td><?= htmlspecialchars($data['orderReceived']); ?></td>
                            <td><?= htmlspecialchars($data['deadline']); ?></td>
                            <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                            <td><?= htmlspecialchars($data['leadTime']); ?></td>
                           
                        </tr>

                        <!-- Modal for editing each row -->
                        <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['poNumber']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $data['poNumber']; ?>">Edit Marketing Department</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>" />

                                            <div class="form-group">
                                                <label for="receivedOrder">Received Order</label>
                                                <select class="form-control" name="receivedOrder" id="receivedOrder" required>
                                                    <option value="Not Started" <?= $data['receivedOrder'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['receivedOrder'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['receivedOrder'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="businessAward">Business Award</label>
                                                <select class="form-control" name="businessAward" id="businessAward" required>
                                                    <option value="Not Started" <?= $data['businessAward'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['businessAward'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['businessAward'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="endorsedToGM">Endorsed to GM</label>
                                                <select class="form-control" name="endorsedToGM" id="endorsedToGM" required>
                                                    <option value="Not Started" <?= $data['endorsedToGM'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                    <option value="In Progress" <?= $data['endorsedToGM'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?= $data['endorsedToGM'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="leadTime">Lead Time</label>
                                                <input type="number" class="form-control" name="leadTime" value="<?= htmlspecialchars($data['leadTime']); ?>" />
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9">No data found in the Marketing Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.modal').forEach(modal => {
            const receivedOrderSelect = modal.querySelector('[name="receivedOrder"]');
            const businessAwardSelect = modal.querySelector('[name="businessAward"]');
            const endorsedToGMSelect = modal.querySelector('[name="endorsedToGM"]');

            const toggleDisableState = () => {
                const isReceivedOrderCompleted = receivedOrderSelect.value === 'Completed';
                const isBusinessAwardCompleted = businessAwardSelect.value === 'Completed';

                businessAwardSelect.disabled = !isReceivedOrderCompleted;
                endorsedToGMSelect.disabled = !isBusinessAwardCompleted;

                if (!isReceivedOrderCompleted) {
                    businessAwardSelect.value = 'Not Started';
                }
                if (!isBusinessAwardCompleted) {
                    endorsedToGMSelect.value = 'Not Started';
                }
            };

            toggleDisableState();

            receivedOrderSelect.addEventListener('change', toggleDisableState);
            businessAwardSelect.addEventListener('change', toggleDisableState);
        });
    });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
