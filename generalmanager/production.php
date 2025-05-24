<?php
// Include the database connection
include '../db.php'; // Adjust the path to your database connection file

// Fetch data from the ProductionTable
$query = "SELECT * FROM production";
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    $productionData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $productionData = [];
}

// Handle the update functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $newLeadTime = intval($_POST['leadTime']); // Get updated lead time

    // Fetch the current dateReceived for the specified PO Number
    $query = "SELECT dateReceived FROM production WHERE poNumber = ?";
    $stmt1 = $conn->prepare($query);
    $stmt1->bind_param("s", $poNumber);
    $stmt1->execute();
    $stmt1->bind_result($dateReceived);
    $stmt1->fetch();
    $stmt1->close();

    // Calculate new deadline = dateReceived + leadTime days
    $deadlineDate = new DateTime($dateReceived);
    $deadlineDate->modify("+$newLeadTime days");
    $newDeadline = $deadlineDate->format('Y-m-d');

    // Calculate daysLeft as difference between deadline and today (in days)
    $today = new DateTime();
    $deadlineDT = new DateTime($newDeadline);
    $diff = $today->diff($deadlineDT);
    $daysLeft = (int)$diff->format("%r%a"); // can be negative if past deadline

    if ($daysLeft < 0) {
        $daysLeft = 0; // no negative days left, clamp to 0
    }

    // Update the database: leadTime, daysLeft, deadline
    $updateQuery = "
        UPDATE production 
        SET leadTime = ?, 
            daysLeft = ?, 
            deadline = ? 
        WHERE poNumber = ?";
    $stmt2 = $conn->prepare($updateQuery);
    $stmt2->bind_param("iiss", $newLeadTime, $daysLeft, $newDeadline, $poNumber);

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
    <title>Production Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2><b>Production Table</b></h2>
        <hr />

        <div class="table-div" style="overflow-x:auto;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>PO No.</th>
                        <th>Finishing</th>
                        <th>Packed</th>
                        <th>Inspected</th>
                        <th>Date Received</th>
                        <th>Deadline</th>
                        <th>Days Left</th>
                        <th>Lead Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productionData)) : ?>
                        <?php foreach ($productionData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['poNumber']); ?></td>
                                <td><?= htmlspecialchars($data['finishing']); ?></td>
                                <td><?= htmlspecialchars($data['packed']); ?></td>
                                <td><?= htmlspecialchars($data['inspected']); ?></td>
                                <td><?= htmlspecialchars($data['dateReceived']); ?></td>
                                <td><?= htmlspecialchars($data['deadline']); ?></td>
                                <td><?= htmlspecialchars($data['daysLeft']); ?></td>
                                <td><?= htmlspecialchars($data['leadTime']); ?></td>
                                   <td style="text-align:center; border-color:transparent;">
                                    <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>" style="border: none; background: none; padding: 0; outline: none;">
                                    <img src="../assets/edit2.png" alt="Edit" />
                                </button>
                                </td>
                            </tr>

                            <!-- Modal for editing leadTime -->
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
                                                    <label for="leadTime<?= htmlspecialchars($data['poNumber']); ?>">Lead Time (Days)</label>
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
                            <td colspan="9">No data found in the Production Table.</td>
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
