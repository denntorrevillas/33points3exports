<?php
include '../db.php';

// Fetch shipping data
$query = "SELECT * FROM shipping";
$result = $conn->query($query);
$shippingData = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Handle leadTime update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $poNumber = $_POST['poNumber'];
    $leadTimeInput = $_POST['leadTime'];

    // Validate date format YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $leadTimeInput)) {
        echo "<script>alert('Invalid date format. Use YYYY-MM-DD.');</script>";
    } else {
        $updateQuery = "UPDATE shipping SET leadTime = ? WHERE poNumber = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ss", $leadTimeInput, $poNumber);

        if ($stmt->execute()) {
            echo "<script>alert('Lead Time updated successfully!');</script>";
            echo "<script>window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('Error updating lead time: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Shipping Table</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
    <h2><b>Shipping Table</b></h2>
    <hr />
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>PO No.</th>
                    <th>Pre-loading</th>
                    <th>Loading</th>
                    <th>Transported</th>
                    <th>Delivered to Customer</th>
                    <th>Date Received</th>
                    <th>Deadline</th>
                    <th>Days Left</th>
                    <th>Lead Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($shippingData)): ?>
                    <?php foreach ($shippingData as $row): ?>
                        <tr>
                            <td>PO<?= htmlspecialchars($row['poNumber']); ?></td>
                            <td><?= htmlspecialchars($row['pre_loading']); ?></td>
                            <td><?= htmlspecialchars($row['loading']); ?></td>
                            <td><?= htmlspecialchars($row['transported']); ?></td>
                            <td><?= htmlspecialchars($row['delivered_to_customer']); ?></td>
                            <td><?= htmlspecialchars($row['dateReceived']); ?></td>
                            <td><?= htmlspecialchars($row['deadline']); ?></td>
                            <td><?= htmlspecialchars($row['daysLeft']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $row['poNumber']; ?>">
                                    <?= htmlspecialchars($row['leadTime']); ?>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal: Edit Lead Time -->
                        <div class="modal fade" id="editModal<?= $row['poNumber']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $row['poNumber']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $row['poNumber']; ?>">Update Lead Time for PO<?= $row['poNumber']; ?></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="poNumber" value="<?= $row['poNumber']; ?>" />
                                            <div class="form-group">
                                                <label for="leadTime">Lead Time (Date)</label>
                                                <input type="date" name="leadTime" id="leadTime" class="form-control" value="<?= htmlspecialchars($row['leadTime']); ?>" required />
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update" class="btn btn-primary">Save Lead Time</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center">No shipping data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
