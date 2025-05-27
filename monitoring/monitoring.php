<?php
include '../db.php';

// Fetch data including fullName by joining with staff table
$query = "
    SELECT m.*, CONCAT(s.firstName, ' ', s.lastName) AS fullName 
    FROM monitoring m
    LEFT JOIN staff s ON m.staff_ID = s.staff_ID
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $monitoringData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $monitoringData = [];
}

$statuses = ['Not Started', 'In Progress', 'Completed'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $newLeadTime = intval($_POST['leadTime']);

    // Additional fields from POST
    $supplierEvaluated = $_POST['supplierEvaluated'];
    $supplierPOCreated = $_POST['supplierPOCreated'];
    $gmApproved = $_POST['gmApproved'];
    $supplierPOIssued = $_POST['supplierPOIssued'];

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

    // Update query with new fields
    $updateQuery = "
        UPDATE monitoring 
        SET leadTime = ?, 
            daysLeft = ?, 
            deadline = ?,
            supplierEvaluated = ?,
            supplierPOCreated = ?,
            gmApproved = ?,
            supplierPOIssued = ?
        WHERE poNumber = ?";

    $stmt2 = $conn->prepare($updateQuery);
    $stmt2->bind_param(
        "iissssss", 
        $newLeadTime, 
        $updatedDaysLeft, 
        $newDeadline, 
        $supplierEvaluated, 
        $supplierPOCreated, 
        $gmApproved, 
        $supplierPOIssued, 
        $poNumber
    );

    if ($stmt2->execute()) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Record updated successfully!',
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
    <div class="container">
        <h2><b>Monitoring</b></h2>
        <hr>

        <div class="table-div">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Full Name</th>
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
                                <td><?= htmlspecialchars($data['fullName'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($data['supplierEvaluated']); ?></td>
                                <td><?= htmlspecialchars($data['supplierPOCreated']); ?></td>
                                <td><?= htmlspecialchars($data['gmApproved']); ?></td>
                                <td><?= htmlspecialchars($data['supplierPOIssued']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td><?= htmlspecialchars($data['leadTime']); ?></td>
                                <td style="text-align:center;">
                                    <button data-toggle="modal" data-target="#editModal<?= htmlspecialchars($data['poNumber']); ?>" style="border: none; background-color:transparent;">
                                        <img src="../assets/edit2.png" alt="Edit" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for editing -->
                            <div class="modal fade" id="editModal<?= htmlspecialchars($data['poNumber']); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= htmlspecialchars($data['poNumber']); ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= htmlspecialchars($data['poNumber']); ?>">Edit Monitoring Data</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="poNumber" value="<?= htmlspecialchars($data['poNumber']); ?>" />

                                                <div class="form-group">
                                                    <label for="supplierEvaluated<?= htmlspecialchars($data['poNumber']); ?>">Supplier Evaluated</label>
                                                    <select name="supplierEvaluated" class="form-control" id="supplierEvaluated<?= htmlspecialchars($data['poNumber']); ?>" required>
                                                        <?php foreach ($statuses as $status): ?>
                                                            <option value="<?= $status; ?>" <?= ($data['supplierEvaluated'] === $status) ? 'selected' : ''; ?>><?= $status; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="supplierPOCreated<?= htmlspecialchars($data['poNumber']); ?>">Supplier PO Created</label>
                                                    <select name="supplierPOCreated" class="form-control" id="supplierPOCreated<?= htmlspecialchars($data['poNumber']); ?>" required>
                                                        <?php foreach ($statuses as $status): ?>
                                                            <option value="<?= $status; ?>" <?= ($data['supplierPOCreated'] === $status) ? 'selected' : ''; ?>><?= $status; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="gmApproved<?= htmlspecialchars($data['poNumber']); ?>">GM Approved</label>
                                                    <select name="gmApproved" class="form-control" id="gmApproved<?= htmlspecialchars($data['poNumber']); ?>" required>
                                                        <?php foreach ($statuses as $status): ?>
                                                            <option value="<?= $status; ?>" <?= ($data['gmApproved'] === $status) ? 'selected' : ''; ?>><?= $status; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                               <div class="form-group">
                                                    <label for="supplierPOIssued<?= htmlspecialchars($data['poNumber']); ?>">Supplier PO Issued</label>
                                                    <select name="supplierPOIssued" class="form-control" id="supplierPOIssued<?= htmlspecialchars($data['poNumber']); ?>" required>
                                                        <?php foreach ($statuses as $status): ?>
                                                            <option value="<?= $status; ?>" <?= ($data['supplierPOIssued'] === $status) ? 'selected' : ''; ?>><?= $status; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                    
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
                            <td colspan="11">No data found in the Monitoring Table.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const daysLeftColumnIndex = 8; // Correct zero-based index for Days Left column

            const rows = document.querySelectorAll("table tbody tr");

            rows.forEach(row => {
                const cells = row.querySelectorAll("td");
                if (cells[daysLeftColumnIndex]) {
                    const value = parseInt(cells[daysLeftColumnIndex].textContent.trim(), 10);

                    if (!isNaN(value)) {
                        if (value >= 10) {
                            cells[daysLeftColumnIndex].style.backgroundColor = "green";
                            cells[daysLeftColumnIndex].style.color = "white";
                        } else if (value >= 4 && value <= 9) {
                            cells[daysLeftColumnIndex].style.backgroundColor = "orange";
                            cells[daysLeftColumnIndex].style.color = "white";
                        } else if (value >= 2 && value <= 3) {
                            cells[daysLeftColumnIndex].style.backgroundColor = "yellow";
                            cells[daysLeftColumnIndex].style.color = "black";
                        } else if (value <= 1) {
                            cells[daysLeftColumnIndex].style.backgroundColor = "red";
                            cells[daysLeftColumnIndex].style.color = "white";
                        }
                    }
                }
            });
        });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
