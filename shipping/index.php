<?php
// Start the session
session_start();

// Include the database connection
include 'db.php';

// Initialize alert script
$alertScript = '';

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Check if user exists in the database
    $sql = "SELECT * FROM staff WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error); // Debug database connection issue
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Debug: Check database query results
        // echo "<pre>"; print_r($row); exit;

        // Verify password
        if (password_verify($password, $row['password'])) {
            // Store staff ID in session
            $_SESSION['staff_ID'] = $row['staff_ID'];

            // Redirect based on department
            $department = $row['department'];
            switch ($department) {
                case 'Marketing':
                    header('Location: marketing/');
                    exit;
                case 'Accounting':
                    header('Location: accounting/');
                    exit;
                case 'Monitoring':
                    header('Location: monitoring/');
                    exit;
                case 'Production':
                    header('Location: production/');
                    exit;
                case 'Shipping':
                    header('Location: shipping/');
                    exit;
                default:
                    // Debug unexpected department
                    die("Unexpected department: $department");
            }
        } else {
            // Invalid password
            $alertScript = "
            <script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Invalid email or password.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    } else {
        // User not found
        $alertScript = "
        <script>
            Swal.fire({
                title: 'Error!',
                text: 'No account found with this email.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link rel="stylesheet" href="../styles/style.css?v=<?php echo time(); ?>">

    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="top">
        <div class="logo-img">
            <img src="../assets/logo1.png" alt="">
        </div>
          <div class="user-logout">
            <div class="dropdown">
               
                <div class="user-logout">
                    <img src="../assets/user.png" alt="" srcset="">
                    <p id="staffname"><?php echo htmlspecialchars($username); ?></p>
                   
                </div>
            </div>
        </div>
    </div>
    <div class="main-div">
        <!-- Navigation -->
        <div class="nav-div">
            <div class="navigations">
                <br>
                <h4><b>Shipping Department</b></h4>
                <hr>
             
               <ul>       
                     <div class="nav-li">
                        <img src="../assets/dashboard.png" alt="">
                        <a href="?page=dashboard">Dashboard</a>
                    </div>

                    <div class="nav-li">
                        <img src="../assets/marketing.png" alt="">
                        <a href="?page=marketing">Marketing Department</a>
                    </div>

                    <div class="nav-li">
                        <img src="../assets/accounting.png" alt="">
                        <a href="?page=accounting">Accounting Department</a>
                    </div> 

                    <div class="nav-li">
                        <img src="../assets/monitoring.png" alt="">
                        <a href="?page=monitoring">Monitoring Department</a>
                    </div>   
                    
                    <div class="nav-li">
                        <img src="../assets/production.png" alt="">
                        <a href="?page=production">Production Department</a>
                    </div>  

                    <div class="nav-li">
                        <img src="../assets/shipping.png" alt="">
                        <a href="?page=shipping">Shippping Department</a>
                    </div>  

                    <div class="nav-li">
                        <img src="../assets/track.png" alt="">
                        <a href="?page=tracking">Track Order</a>
                    </div> 

                     <div class="nav-li">
                        <img src="../assets/archive.png" alt="">
                        <a href="?page=archive">Shipping History</a>
                    </div> 
                </ul>
            </div>

            <div class="logout-div">
            <hr>
                    <div class="log-out">
                        <img src="../assets/logout.png" alt="" srcset="">
                            <a href="">Log Out</a>
                    </div>
           </div>
        </div>

        <!-- Content Area -->
        <div class="table-div">
            <div class="table-div-content">
                <!-- Search Bar -->
               

                <div class="content">
                    <?php
                    // Determine which page to include
                    if (isset($_GET['page'])) {
                        $page = $_GET['page'];
                        $allowed_pages = ['archive','tracking','production','marketing','dashboard', 'accounting', 'monitoring','shipping']; // Whitelist pages

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

    <!-- JavaScript for Search Functionality -->
    <script>
        // Get the input field and table data elements
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const contentDiv = document.querySelector('.content');
            const rows = contentDiv.getElementsByTagName('tr'); // Assuming content is in a table

            // Loop through all rows and hide those that don't match the search term
            for (let i = 0; i < rows.length; i++) {
                let row = rows[i];
                const cells = row.getElementsByTagName('td');
                let match = false;

                // Loop through each cell in the row to check if any content matches the search term
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        if (cells[j].textContent.toLowerCase().includes(filter)) {
                            match = true;
                        }
                    }
                }

                // Show or hide the row based on the match
                if (match) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });


        function updateDateTime() {
            const now = new Date();
            const dateString = now.toLocaleDateString(); // Gets the current date in the local format
            const timeString = now.toLocaleTimeString(); // Gets the current time in the local format

            document.getElementById("date-time").textContent = `${dateString} - ${timeString}`;
        }

        setInterval(updateDateTime, 1000); // Updates the time every second
    </script>
     <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <!-- Bootstrap JS (for dropdown functionality) -->
    <script src="https://cosde.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQzM2pPp8PpVfEjxQv5L+0X2n9Vo6+gmG69K9B" crossorigin="anonymous"></script>
</body>
</html>
