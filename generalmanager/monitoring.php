<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the MonitoringTable
$query = "SELECT * FROM monitoring";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    $monitoringData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $monitoringData = [];
}

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $newLeadTime = intval($_POST['leadTime']);

    // Get current daysLeft and dateReceived for this poNumber
    $query = "SELECT daysLeft, dateReceived FROM monitoring WHERE poNumber = ?";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("s", $poNumber);
    $stmt1->execute();
    $stmt1->bind_result($currentDaysLeft, $dateReceived);
    $stmt1->fetch();
    $stmt1->close();

    // Calculate updated daysLeft by adding the new leadTime input
    $updatedDaysLeft = intval($currentDaysLeft) + $newLeadTime;

    // Calculate new deadline = dateReceived + updatedDaysLeft days
    $deadlineDate = new DateTime($dateReceived);
    $deadlineDate->modify("+$updatedDaysLeft days");
    $newDeadline = $deadlineDate->format('Y-m-d');

    // Update only leadTime, daysLeft, deadline
    $updateQuery = "
        UPDATE monitoring 
        SET leadTime = ?, 
            daysLeft = ?, 
            deadline = ?
        WHERE poNumber = ?";
    $stmt2 = $conn->prepare($updateQuery);
    $stmt2->bind_param("iiss", $newLeadTime, $updatedDaysLeft, $newDeadline, $poNumber);

    if ($stmt2->execute()) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Lead Time, Days Left, and Deadline updated successfully!',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = window.location.href;
                }
            });
        </script>
        ";
        exit;
    } else {
        echo "<script>alert('Error updating record: " . $stmt2->error . "');</script>";
    }

    $stmt2->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Monitoring Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-4">
        <h2><b>Monitoring</b></h2>

        <div class="table-div">
            <table class="table table-bordered table-striped">
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
                        <th>Action</th>
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
                                <td style="text-align:center;">
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editModal<?= htmlspecialchars($data['poNumber']); ?>">
                                        <img src="../assets/edit2.png" alt="Edit" style="height:20px; width:20px;" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing leadTime only -->
                            <div class="modal fade" id="editModal<?= htmlspecialchars($data['poNumber']); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= htmlspecialchars($data['poNumber']); ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= htmlspecialchars($data['poNumber']); ?>">Edit Lead Time</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= htmlspecialchars($data['poNumber']); ?>" />
                                                <div class="form-group">
                                                    <label for="leadTime<?= htmlspecialchars($data['poNumber']); ?>">Lead Time (in days)</label>
                                                    <input
                                                        type="number"
                                                        name="leadTime"
                                                        class="form-control"
                                                        id="leadTime<?= htmlspecialchars($data['poNumber']); ?>"
                                                        value="<?= htmlspecialchars($data['leadTime']); ?>"
                                                        min="0"
                                                        required
                                                    />
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

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
