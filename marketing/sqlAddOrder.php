<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        empty($_POST['poNumber']) || 
        empty($_POST['leadTime']) || 
        empty($_POST['buyer'])
    ) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'All fields are required.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit;
    }

    $poNumber = $_POST['poNumber'];
    $buyer = $_POST['buyer'];
    $leadTime = (int)$_POST['leadTime'];

    $orderDate = date('Y-m-d');
    $shipDate = date('Y-m-d', strtotime("+$leadTime days"));
    $daysLeft = $leadTime;
    $overallStatus = "Not Started";

    $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, daysLeft, leadTime, overallStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssiss", $poNumber, $buyer, $orderDate, $shipDate, $daysLeft, $leadTime, $overallStatus);

        if ($stmt->execute()) {
            // SweetAlert2 success script
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Order has been added successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'dashboard.php'; // Redirect on confirmation
                    }
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Execution error: " . $stmt->error . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Query error: " . $conn->error . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }

    $conn->close();
} else {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'Invalid request method.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>";
}
?>
