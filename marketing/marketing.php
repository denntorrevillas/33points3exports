<?php
include '../db.php'; // Assuming the database connection is in db.php

// Modified query: LEFT JOIN to avoid missing marketing rows without staff
$query = "SELECT 
    m.marketingID,
    m.poNumber,
    m.receivedOrder,
    m.businessAward,
    m.endorsedToGM,
    m.orderReceived,
    m.deadline,
    m.daysLeft,
    m.leadTime,
    m.to_delete,
    m.staff_ID,
    CONCAT(s.firstname, ' ', s.lastname) AS fullname
FROM 
    marketing m
LEFT JOIN 
    staff s ON m.staff_ID = s.staff_ID";

$result = $conn->query($query);

if (!$result) {
    die("Query Error: " . $conn->error);
}

if ($result->num_rows > 0) {
    $marketingData = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $marketingData = [];
    echo "<p>No marketing records found.</p>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $receivedOrderNew = $_POST['receivedOrder'] ?? 'Not Started';
    $businessAwardNew = $_POST['businessAward'] ?? 'Not Started';
    $endorsedToGMNew = $_POST['endorsedToGM'] ?? 'Not Started';
    $leadTimeNew = isset($_POST['leadTime']) && $_POST['leadTime'] !== '' ? $_POST['leadTime'] : NULL;
    $staff_ID = $_SESSION['staff_ID'] ?? 'Unknown';

    // Fetch old values for comparison
    $fetchOld = $conn->prepare("SELECT receivedOrder, businessAward, endorsedToGM, leadTime FROM marketing WHERE poNumber = ?");
    $fetchOld->bind_param("s", $poNumber);
    $fetchOld->execute();
    $fetchOld->store_result();

    $fetchOld->bind_result($receivedOrderOld, $businessAwardOld, $endorsedToGMOld, $leadTimeOld);
    $fetchOld->fetch();
    $fetchOld->close();

    // Update marketing table
    $updateQuery = "UPDATE marketing SET receivedOrder = ?, businessAward = ?, endorsedToGM = ?, leadTime = ?, staff_ID = ? WHERE poNumber = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssss", $receivedOrderNew, $businessAwardNew, $endorsedToGMNew, $leadTimeNew, $staff_ID, $poNumber);

    if ($stmt->execute()) {
        // Prepare to insert into history table
        $department = 'marketing';

        $historyInsert = $conn->prepare("INSERT INTO history (poNumber, columnName, oldValue, newValue, actionBy, department) VALUES (?, ?, ?, ?, ?, ?)");

        // Check each changed column and insert into history
        if ($receivedOrderOld !== $receivedOrderNew) {
            $columnName = 'receivedOrder';
            $historyInsert->bind_param("ssssss", $poNumber, $columnName, $receivedOrderOld, $receivedOrderNew, $staff_ID, $department);
            $historyInsert->execute();
        }
        if ($businessAwardOld !== $businessAwardNew) {
            $columnName = 'businessAward';
            $historyInsert->bind_param("ssssss", $poNumber, $columnName, $businessAwardOld, $businessAwardNew, $staff_ID, $department);
            $historyInsert->execute();
        }
        if ($endorsedToGMOld !== $endorsedToGMNew) {
            $columnName = 'endorsedToGM';
            $historyInsert->bind_param("ssssss", $poNumber, $columnName, $endorsedToGMOld, $endorsedToGMNew, $staff_ID, $department);
            $historyInsert->execute();
        }
        if ((string)$leadTimeOld !== (string)$leadTimeNew) {
            $columnName = 'leadTime';
            $oldVal = $leadTimeOld === null ? 'NULL' : $leadTimeOld;
            $newVal = $leadTimeNew === null ? 'NULL' : $leadTimeNew;
            $historyInsert->bind_param("ssssss", $poNumber, $columnName, $oldVal, $newVal, $staff_ID, $department);
            $historyInsert->execute();
        }

        $historyInsert->close();

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Update Successful',
                text: 'Record updated successfully!',
                confirmButtonText: 'OK'
            });
        </script>";
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

    <div class="table-div">
        <table class="table">
            <tr>
                <th>PO No.</th>
                <th>Received Order</th>
                <th>Business Award</th>
                <th>Endorsed to GM</th>
                <th>Order Received</th>
                <th>Deadline</th>
                <th>Days Left</th>
                <th>Lead Time</th>
                <th>Last Modified by</th>
                <th>Action</th>
            </tr>
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
                            <td><?= htmlspecialchars($data['fullname']); ?></td>
                            <td>
                                <button data-toggle="modal" data-target="#editModal<?= $data['poNumber']; ?>" style="border: none;background-color:transparent;s">
                                    <img src="../assets/edit2.png" alt="Edit" />
                                </button>
                            </td>
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
                        <td class="text-center" colspan="10">No data found in the Marketing Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const targetColumns = [6]; // Column Days Left
            const rows = document.querySelectorAll("table tr");

            rows.forEach(row => {
                const cells = row.querySelectorAll("td");

                targetColumns.forEach(columnIndex => {
                    if (cells[columnIndex]) {
                        const value = parseInt(cells[columnIndex].textContent, 10); // Convert cell content to an integer

                        if (value > 10) {
                            cells[columnIndex].style.backgroundColor = "green";
                            cells[columnIndex].style.color = "white"; // Change text color to white
                        } else if (value >= 4 && value <= 9) {
                            cells[columnIndex].style.backgroundColor = "orange";
                            cells[columnIndex].style.color = "white";
                        } else if (value >= 2 && value <= 3) {
                            cells[columnIndex].style.backgroundColor = "yellow";
                            cells[columnIndex].style.color = "black";
                        } else if (value <= 1) {
                            cells[columnIndex].style.backgroundColor = "red";
                            cells[columnIndex].style.color = "white";
                        }
                    }
                });
            });
        });

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
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
