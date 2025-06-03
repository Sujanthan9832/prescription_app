<?php
session_start();
include 'db.php';

if (!isset($_SESSION['pharmacy_logged_in'])) {
    echo "Access denied.";
    exit();
}

// Get prescription ID
if (!isset($_GET['id'])) {
    echo "No prescription selected.";
    exit();
}

$prescription_id = (int) $_GET['id'];

// Get prescription data
$stmt = $conn->prepare("SELECT p.*, u.name, u.email FROM prescriptions p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$prescription = $stmt->get_result()->fetch_assoc();

if (!$prescription) {
    echo "Prescription not found.";
    exit();
}

// Get images
$images = [];
$img_stmt = $conn->prepare("SELECT file_path FROM prescription_images WHERE prescription_id = ?");
$img_stmt->bind_param("i", $prescription_id);
$img_stmt->execute();
$img_result = $img_stmt->get_result();
while ($row = $img_result->fetch_assoc()) {
    $images[] = $row['file_path'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $newItem = [
        'drug' => $_POST['drug'],
        'quantity' => (int)$_POST['quantity'],
        'price' => (float)$_POST['price']
    ];

    if (!isset($_SESSION['quotation_items'][$prescription_id])) {
        $_SESSION['quotation_items'][$prescription_id] = [];
    }
    $_SESSION['quotation_items'][$prescription_id][] = $newItem;
}

$quotation_items = $_SESSION['quotation_items'][$prescription_id] ?? [];
?>

<?php include 'components/admin_header.php'; ?>

<div class= "dashboardContent">
<script>
        function showPreview(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
    <h2>Prescription Quotation</h2>
    <p><strong>User:</strong> <?= htmlspecialchars($prescription['name']) ?> (<?= $prescription['email'] ?>)</p>
    <p><strong>Note:</strong> <?= htmlspecialchars($prescription['note']) ?></p>
    <p><strong>Delivery:</strong> <?= htmlspecialchars($prescription['delivery_address']) ?> at <?= $prescription['delivery_time_slot'] ?></p>
    <br/>

    <div style="display: flex;">
        <div style="margin-right: 20px;">
            <?php if (count($images)): ?>
                <img id="mainImage" src="<?= $images[0] ?>" class="preview"><br>
                <div class="thumbs">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= $img ?>" onclick="showPreview(this.src)">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No image uploaded.</p>
            <?php endif; ?>
        </div>

        <div style="flex: 1">
        <h3>Quotation</h3>
        <table>
            <tr>
                <th>Drug</th>
                <th>Quantity</th>
                <th>Amount</th>
            </tr>
            <?php
            $total = 0;
            foreach ($quotation_items as $item):
                $amount = $item['price'] * $item['quantity'];
                $total += $amount;
            ?>
                <tr>
                    <td><?= htmlspecialchars($item['drug']) ?></td>
                    <td><?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?></td>
                    <td><?= number_format($amount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" style="text-align: right;"><strong>Total</strong></td>
                <td><strong><?= number_format($total, 2) ?></strong></td>
            </tr>
        </table>

        <!-- Add new drug item -->
        <form method="post" action="view_prescription.php?id=<?= $prescription_id ?>" style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px; align-items: flex-end;">
            <div style="display: flex; align-items: center; gap: 5px; justify-content: flex-end;">
            <label for="drug" style="min-width: 80px; text-align: right;">Drug name:</label>
            <input type="text" id="drug" name="drug" placeholder="Drug name" required />
            </div>
            <div style="display: flex; align-items: center; gap: 5px; justify-content: flex-end;">
            <label for="quantity" style="min-width: 70px; text-align: right;">Quantity:</label>
            <input type="number" id="quantity" name="quantity" placeholder="Quantity" required min="1" />
            </div>
            <div style="display: flex; align-items: center; gap: 5px; justify-content: flex-end;">
            <label for="price" style="min-width: 90px; text-align: right;">Price per unit:</label>
            <input type="number" step="0.01" id="price" name="price" placeholder="Price per unit" required />
            </div>
            <input type="submit" name="add_item" value="Add" class="secondtryBtn" style="margin-left: 10px;" />
        </form>

        
        <hr style="width:100%; margin: 30px 0;">
        <!-- Send quotation -->
        <form method="post" action="send_quotation.php" style="display: flex; flex-direction: column; align-items: flex-end;">
                <input type="hidden" name="prescription_id" value="<?= $prescription_id ?>">
                <input type="submit" name="send_quotation" value="Send Quotation" style="margin-top: 20px;" class="secondtryBtn" />
        </form>
    </div>
    </div>
    </div>
</body>
</html>
