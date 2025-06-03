<?php
session_start();
include 'db.php';

// Simulate pharmacy login (for demo only)
$_SESSION['pharmacy_logged_in'] = true;

if (!isset($_SESSION['pharmacy_logged_in'])) {
    echo "Access denied.";
    exit();
}

// Fetch all prescriptions with user info and quotation status
$sql = "SELECT p.*, u.name, u.email, u.contact_no, q.status AS quote_status
        FROM prescriptions p 
        JOIN users u ON p.user_id = u.id
        LEFT JOIN quotations q ON p.id = q.prescription_id
        ORDER BY p.created_at DESC";

$result = $conn->query($sql);
?>

<?php include 'components/admin_header.php'; ?>
<div class="dashboardContent">
    <h2 class="titleGap">Pharmacy Dashboard</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
            $status = strtolower($row['quote_status'] ?? 'pending');

            // Define styles for status
            switch ($status) {
                case 'accepted':
                    $bgColor = '#d4edda'; $textColor = '#155724'; break;
                case 'rejected':
                    $bgColor = '#f8d7da'; $textColor = '#721c24'; break;
                case 'quoted':
                    $bgColor = '#fff3cd'; $textColor = '#856404'; break;
                default:
                    $bgColor = '#e2e3e5'; $textColor = '#383d41'; $status = 'pending';
            }
        ?>
        <div style="border:1px solid #ccc; background-color: <?= $bgColor ?>; padding:10px; margin-bottom:15px;">
            <strong>User:</strong> <?= htmlspecialchars($row['name']) ?> (<?= $row['email'] ?>)<br>
            <strong>Contact:</strong> <?= htmlspecialchars($row['contact_no']) ?><br>
            <strong>Note:</strong> <?= htmlspecialchars($row['note']) ?><br>
            <strong>Delivery Address:</strong> <?= htmlspecialchars($row['delivery_address']) ?><br>
            <strong>Time Slot:</strong> <?= htmlspecialchars($row['delivery_time_slot']) ?><br>
            <strong>Submitted At:</strong> <?= $row['created_at'] ?><br><br>

            <strong>Status:</strong> 
            <span style="font-weight:bold; color: <?= $textColor ?>;">
                <?= ucfirst($status) ?>
            </span>
            <br><br>

            <strong>Files:</strong><br>
            <ul style="list-style-type: none; padding-left: 0; gap: 10px; display: flex; flex-wrap: wrap;">
            <?php
            $pres_id = $row['id'];
            $stmt = $conn->prepare("SELECT file_path FROM prescription_images WHERE prescription_id = ?");
            $stmt->bind_param("i", $pres_id);
            $stmt->execute();
            $files = $stmt->get_result();

            while ($f = $files->fetch_assoc()) {
                $path = htmlspecialchars($f['file_path']);
                echo "<li class='secondtryBtn'><a href='$path' target='_blank'>View File</a></li>";
            }
            ?>
            </ul>

            <a href="view_prescription.php?id=<?= $row['id'] ?>">
                <button class="btn fixedWidhBtn">View & Prepare Quotation</button>
            </a>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No prescriptions uploaded yet.</p>
    <?php endif; ?>
</div>
<?php include 'components/footer.php'; ?>
