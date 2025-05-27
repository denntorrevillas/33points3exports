<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['staff_ID'])) {
    header('Location: ../index.php'); // Redirect to login if not logged in
    exit;
}

// Include database connection
include '../db.php';

// Retrieve staff details from the database
$staff_id = $_SESSION['staff_ID'];
$sql = "SELECT CONCAT(firstname, ' ', middlename, ' ', lastname) AS username, department FROM staff WHERE staff_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $staff_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['username'];
    $department = $row['department'];

    // Check if the staff is from the marketing department
    if ($department !== 'Marketing') {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8" />
            <title>Access Denied</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Access Denied",
                    text: "You are not authorized to access this page!",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location.href = "../index.php";
                });
            </script>
        </body>
        </html>';
        exit;
    }
} else {
    $username = "Unknown User"; // Fallback if user details are not found
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Marketing Department</title>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
      integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
      crossorigin="anonymous"
    />
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" />
    <link rel="stylesheet" href="../styles/style.css?v=<?php echo time(); ?>" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="top">
        <div class="logo-img">
            <img src="../assets/logo1.png" alt="" />
        </div>
        <div class="user-logout">
            <div class="dropdown">
                <div class="user-logout">
                    <img src="../assets/user.png" alt="" />
                    <p id="staffname"><?php echo htmlspecialchars($username); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="main-div">
        <!-- Navigation -->
        <div class="nav-div">
            <div class="navigations">
                <br />
                <h4><b>Marketing Department</b></h4>
                <hr />
                <ul>
                    <div class="nav-li">
                        <img src="../assets/dashboard.png" alt="" />
                        <a href="?page=dashboard">Dashboard</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/add.png" alt="" />
                        <a href="?page=add_order">Add Order</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/marketing.png" alt="" />
                        <a href="?page=marketing">Marketing Department</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/accounting.png" alt="" />
                        <a href="?page=accounting">Accounting Department</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/monitoring.png" alt="" />
                        <a href="?page=monitoring">Monitoring Department</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/production.png" alt="" />
                        <a href="?page=production">Production Department</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/shipping.png" alt="" />
                        <a href="?page=shipping">Shipping Department</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/track.png" alt="" />
                        <a href="?page=tracking">Track Order</a>
                    </div>
                    <div class="nav-li">
                        <img src="../assets/archive.png" alt="" />
                        <a href="?page=archive">Marketing Archive</a>
                    </div>
                    <hr />
                    <div class="nav-li">
                        <img src="../assets/log.png" alt="" />
                        <a href="../logout.php">Log out</a>
                    </div>
                </ul>
            </div>
        </div>

        <!-- Content Area -->
        <div class="table-div">
            <div class="table-div-content">
                <div class="content">
                    <?php
                    // Determine which page to include
                    if (isset($_GET['page'])) {
                        $page = $_GET['page'];
                        $allowed_pages = [
                            'archive', 'tracking', 'accounting', 'shipping', 'production', 
                            'monitoring', 'dashboard', 'add_order', 'history', 'track_order', 'marketing'
                        ];

                        if (in_array($page, $allowed_pages)) {
                            include "$page.php";
                        } else {
                            echo "<p>Page not found.</p>";
                        }
                    } else {
                        include "dashboard.php"; // Default page
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
