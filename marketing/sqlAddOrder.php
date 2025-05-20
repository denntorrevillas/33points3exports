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

   $orderDate = date('Y-m-d H:i:s');
    $shipDate = date('Y-m-d', strtotime("+$leadTime days"));
    $daysLeft = $leadTime;
    $overallStatus = "Not Started";

    $sql = "INSERT INTO Orders (poNumber, buyer, orderDate, shipDate, daysLeft, leadTime, overallStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssiss", $poNumber, $buyer, $orderDate, $shipDate, $daysLeft, $leadTime, $overallStatus);

        if ($stmt->execute()) {
           
             echo "<script>alert('Record updated successfully!');
             window.location.reload();
             </script>";
            
       
        } else {
           echo "<script>alert('Record update failed!');</script>";
         echo "<script>window.location.href = window.location.href;</script>";
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
