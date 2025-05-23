<?php
// Include the database connection
include '../db.php'; // Assuming the database connection is in db.php

// Query to fetch data from the staff table
$query = "SELECT id, firstname, middlename, lastname, phone, email, department, position, password, status, accountCreated FROM staff";
$result = $conn->query($query);

// Check if there are any results
$staffData = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-4"><b>Staff Table</b></h2>
        <hr>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Password</th>
                        <th>Status</th>
                        <th>Account Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($staffData)) : ?>
                        <?php foreach ($staffData as $data) : ?>
                            <tr>
                                <td><?= htmlspecialchars($data['id']); ?></td>
                                <td><?= htmlspecialchars($data['firstname']); ?></td>
                                <td><?= htmlspecialchars($data['middlename']); ?></td>
                                <td><?= htmlspecialchars($data['lastname']); ?></td>
                                <td><?= htmlspecialchars($data['phone']); ?></td>
                                <td><?= htmlspecialchars($data['email']); ?></td>
                                <td><?= htmlspecialchars($data['department']); ?></td>
                                <td><?= htmlspecialchars($data['position']); ?></td>
                                <td><?= htmlspecialchars($data['password']); ?></td>
                                <td><?= htmlspecialchars($data['status']); ?></td>
                                <td><?= htmlspecialchars($data['accountCreated']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?= $data['id']; ?>">Edit</button>
                                </td>
                            </tr>

                            <!-- Modal for editing staff details -->
                            <div class="modal fade" id="editModal<?= $data['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $data['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $data['id']; ?>">Edit Staff Details</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST" action="update_staff.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']); ?>">

                                                <div class="form-group">
                                                    <label for="firstname">First Name</label>
                                                    <input type="text" class="form-control" name="firstname" value="<?= htmlspecialchars($data['firstname']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="middlename">Middle Name</label>
                                                    <input type="text" class="form-control" name="middlename" value="<?= htmlspecialchars($data['middlename']); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label for="lastname">Last Name</label>
                                                    <input type="text" class="form-control" name="lastname" value="<?= htmlspecialchars($data['lastname']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="phone">Phone</label>
                                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($data['phone']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['email']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="department">Department</label>
                                                    <input type="text" class="form-control" name="department" value="<?= htmlspecialchars($data['department']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="position">Position</label>
                                                    <input type="text" class="form-control" name="position" value="<?= htmlspecialchars($data['position']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="password">Password</label>
                                                    <input type="password" class="form-control" name="password" value="<?= htmlspecialchars($data['password']); ?>" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select class="form-control" name="status">
                                                        <option value="Active" <?= $data['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="Inactive" <?= $data['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="12">No data found in the staff table.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
