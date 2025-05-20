<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../db.php';

    $poNumber = $_POST['poNumber'] ?? '';

    if (empty($poNumber)) {
        $error = "Please enter a PO Number.";
    } else {
        $sql = "
            SELECT 
                m.poNumber,
                m.orderreceived AS marketing_datereceived,
                m.receivedOrder, m.businessAward, m.endorsedToGM,
                a.datereceived AS accounting_datereceived, a.receivedCopy, a.paymentReceived,
                mo.datereceived AS monitoring_datereceived, mo.supplierEvaluated, mo.supplierPOCreated, mo.gmApproved, mo.supplierPOIssued,
                p.datereceived AS production_datereceived, p.finishing, p.packed, p.inspected,
                s.datereceived AS shipping_datereceived, s.pre_loading, s.loading, s.transported, s.delivered_to_customer
            FROM marketing m
            LEFT JOIN accounting a ON m.poNumber = a.poNumber
            LEFT JOIN monitoring mo ON m.poNumber = mo.poNumber
            LEFT JOIN production p ON m.poNumber = p.poNumber
            LEFT JOIN shipping s ON m.poNumber = s.poNumber
            WHERE m.poNumber = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $poNumber);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $error = "PO Number not found.";
            } else {
                $data = $result->fetch_assoc();
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>PO Tracking Timeline - Minimal</title>
<!-- Poppins Google Font -->
<style>

  .container {
    max-width: 600px;
    margin: auto;
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 8px;
    /* box-shadow: 0 2px 6px rgb(0 0 0 / 0.1); */
  }
  h2 {
    text-align: center;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #111;
  }
  form {
    text-align: center;
    margin-bottom: 2rem;
  }
  input[type="text"] {
    width: 70%;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
  }
  input[type="text"]:focus {
    border-color: #0077ff;
    outline: none;
  }
  button {
    margin-left: 0.8rem;
    background: #1d503a;
    color: white;
    border: none;
    padding: 0.55rem 1.3rem;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
  }
  button:hover {
    background:rgb(23, 85, 58);
  }
  .error {
    color: #cc0000;
    text-align: center;
    margin-bottom: 1rem;
  }

  /* Minimal timeline styles */
  .timeline {
    position: relative;
    margin-left: 1rem;
    border-left: 2px solid #ddd;
    padding-left: 1.2rem;
  }
  .timeline-event {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 1.2rem;
  }
  .timeline-event:last-child {
    margin-bottom: 0;
  }
  .timeline-event::before {
    content: "";
    position: absolute;
    left: -1.3rem;
    top: 6px;
    width: 12px;
    height: 12px;
    background: #1d503a; /* Updated dot color */
    border-radius: 50%;
  }
  h3 {
    margin: 0 0 0.6rem 0;
    font-weight: 600;
    font-size: 1.1rem;
    color: #1d503a;
  }
  .field {
    font-size: 0.95rem;
    color: #555;
    margin-bottom: 0.3rem;
  }
  .label {
    font-weight: 600;
    color: #333;
  }
</style>

</head>
<body>

<div class="container">
  <h2>Track PO Number</h2>
  <form method="POST" action="">
    <input type="text" name="poNumber" placeholder="Enter PO Number" value="<?= htmlspecialchars($poNumber ?? '') ?>" required />
    <button type="submit">Track</button>
  </form>

  <?php if (!empty($error)) : ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($data)) : ?>
    <div class="timeline">
      <div class="timeline-event">
        <h3>Marketing Department</h3>
        <div class="field status"><span class="label">Order Received:</span> <?= htmlspecialchars($data['marketing_datereceived'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Received Order:</span> <?= htmlspecialchars($data['receivedOrder'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Business Award:</span> <?= htmlspecialchars($data['businessAward'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Endorsed To GM:</span> <?= htmlspecialchars($data['endorsedToGM'] ?? 'N/A') ?></div>
      </div>

      <div class="timeline-event">
        <h3>Accounting Department</h3>
        <div class="field status"><span class="label">Order Received:</span> <?= htmlspecialchars($data['accounting_datereceived'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Received Copy:</span> <?= htmlspecialchars($data['receivedCopy'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Payment Received:</span> <?= htmlspecialchars($data['paymentReceived'] ?? 'N/A') ?></div>
      </div>

      <div class="timeline-event">
        <h3>Monitoring Department</h3>
        <div class="field status"><span class="label">Order Received:</span> <?= htmlspecialchars($data['monitoring_datereceived'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Supplier Evaluated:</span> <?= htmlspecialchars($data['supplierEvaluated'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Supplier PO Created:</span> <?= htmlspecialchars($data['supplierPOCreated'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">GM Approved:</span> <?= htmlspecialchars($data['gmApproved'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Supplier PO Issued:</span> <?= htmlspecialchars($data['supplierPOIssued'] ?? 'N/A') ?></div>
      </div>

      <div class="timeline-event">
        <h3>Production Department</h3>
        <div class="field status"><span class="label">Order Received:</span> <?= htmlspecialchars($data['production_datereceived'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Finishing:</span> <?= htmlspecialchars($data['finishing'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Packed:</span> <?= htmlspecialchars($data['packed'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Inspected:</span> <?= htmlspecialchars($data['inspected'] ?? 'N/A') ?></div>
      </div>

      <div class="timeline-event">
        <h3>Shipping Department</h3>
        <div class="field status"><span class="label">Order Received:</span> <?= htmlspecialchars($data['shipping_datereceived'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Pre Loading:</span> <?= htmlspecialchars($data['pre_loading'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Loading:</span> <?= htmlspecialchars($data['loading'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Transported:</span> <?= htmlspecialchars($data['transported'] ?? 'N/A') ?></div>
        <div class="field status"><span class="label">Delivered To Customer:</span> <?= htmlspecialchars($data['delivered_to_customer'] ?? 'N/A') ?></div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
// After page loads, color status texts based on their content
window.addEventListener('DOMContentLoaded', () => {
  const statusElements = document.querySelectorAll('.field.status');

  statusElements.forEach(el => {
    // Get the text content after the label, trimmed and lowercase
    const text = el.textContent.replace(/^[^:]+:\s*/, '').trim().toLowerCase();

    if (text === 'not started') {
      el.style.color = 'red';
    } else if (text === 'in progress') {
      el.style.color = 'orange'; // yellow can be hard to read
    } else if (text === 'completed') {
      el.style.color = 'green';
    }
  });
});
</script>

</body>
</html>
