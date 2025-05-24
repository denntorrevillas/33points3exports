<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the MonitoringTable
$query = "SELECT * FROM monitoring";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    $monitoringData = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate leadTime dynamically if missing
    foreach ($monitoringData as &$data) {
        if (is_null($data['leadTime']) && !is_null($data['dateReceived']) && !is_null($data['deadline'])) {
            $dateReceived = new DateTime($data['dateReceived']);
            $deadline = new DateTime($data['deadline']);
            $leadTime = $deadline->diff($dateReceived)->days;
            $data['leadTime'] = $leadTime;

            // Update the database with the calculated leadTime
            $updateLeadTimeQuery = "UPDATE monitoring SET leadTime = ? WHERE poNumber = ?";
            $stmt = $conn->prepare($updateLeadTimeQuery);
            $stmt->bind_param("is", $leadTime, $data['poNumber']);
            $stmt->execute();
            $stmt->close();
        }
    }
} else {
    $monitoringData = [];
}

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $supplierEvaluated = $_POST['supplierEvaluated'] ?? null;
    $supplierPOCreated = $_POST['supplierPOCreated'] ?? null;
    $gmApproved = $_POST['gmApproved'] ?? null;
    $supplierPOIssued = $_POST['supplierPOIssued'] ?? null;


}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2><b>Monitoring</b></h2>
        <hr>

        <div class="table-div">
            <table class="table">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Supplier Evaluated</th>
                        <th>Supplier PO Created</th>
                        <th>GM Approved</th>
                        <th>Supplier PO Issued</th>
                        <th>Date Received</th>
                        <th>Deadline</th>
                        <th>Days Left</th>
                        <th>Lead Time</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($monitoringData)) : ?>
                        <?php foreach ($monitoringData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['poNumber']); ?></td>
                                <td><?= htmlspecialchars($data['supplierEvaluated']); ?></td>
                                <td><?= htmlspecialchars($data['supplierPOCreated']); ?></td>
                                <td><?= htmlspecialchars($data['gmApproved']); ?></td>
                                <td><?= htmlspecialchars($data['supplierPOIssued']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td><?= htmlspecialchars($data['leadTime']); ?></td>
                              
                            </tr>

                            <!-- Modal for editing each row -->
                            <div class="modal fade" id="editModal<?= $data['poNumber']; ?>" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Monitoring Record</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= $data['poNumber']; ?>">

                                                <!-- Dropdown fields -->
                                                <div class="form-group">
                                                    <label>Supplier Evaluated</label>
                                                    <select name="supplierEvaluated" class="form-control">
                                                        <option value="Not Started" <?= $data['supplierEvaluated'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['supplierEvaluated'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['supplierEvaluated'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Supplier PO Created</label>
                                                    <select name="supplierPOCreated" class="form-control">
                                                        <option value="Not Started" <?= $data['supplierPOCreated'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['supplierPOCreated'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['supplierPOCreated'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>GM Approved</label>
                                                    <select name="gmApproved" class="form-control">
                                                        <option value="Not Started" <?= $data['gmApproved'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['gmApproved'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['gmApproved'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Supplier PO Issued</label>
                                                    <select name="supplierPOIssued" class="form-control">
                                                        <option value="Not Started" <?= $data['supplierPOIssued'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="In Progress" <?= $data['supplierPOIssued'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="Completed" <?= $data['supplierPOIssued'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
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
                            <td colspan="10">No data found in the Monitoring Table.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
