<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = trim($_POST['note']);
    $delivery_address = trim($_POST['delivery_address']);
    $delivery_time = $_POST['delivery_time'];
    $user_id = $_SESSION['user_id'];

    // Validate fields
    if (empty($note) || empty($delivery_address) || empty($delivery_time)) {
        $errors[] = "All fields are required.";
    }

    // Validate files
    if (!isset($_FILES['prescriptions']) || count($_FILES['prescriptions']['name']) > 5) {
        $errors[] = "You can upload up to 5 files.";
    }

    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];

    foreach ($_FILES['prescriptions']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['prescriptions']['error'][$key] !== 0) {
            $errors[] = "File upload error.";
            break;
        }
        if (!in_array($_FILES['prescriptions']['type'][$key], $allowed_types)) {
            $errors[] = "Only JPG, PNG, or PDF files are allowed.";
            break;
        }
    }

    // If valid, save to DB
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, note, delivery_address, delivery_time_slot) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $note, $delivery_address, $delivery_time);
        $stmt->execute();
        $prescription_id = $stmt->insert_id;

        // Upload files
        foreach ($_FILES['prescriptions']['tmp_name'] as $key => $tmp_name) {
            $original_name = basename($_FILES['prescriptions']['name'][$key]);
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $filename = "uploads/" . uniqid() . "." . $extension;

            if (move_uploaded_file($tmp_name, $filename)) {
                $stmt = $conn->prepare("INSERT INTO prescription_images (prescription_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $prescription_id, $filename);
                $stmt->execute();
            }
        }

        $success = "Prescription uploaded successfully!";
    }
}
?>
<?php include 'components/header.php'; ?>


<!-- <!DOCTYPE html>
<html>
<head>
    <title>Upload Prescription</title>
</head>
<body> -->
    <div class="dashboardContent">
    <h2 class="titleGap">Upload Prescription</h2>
    <!-- <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> | <a href="logout.php">Logout</a></p> -->

    <?php
    if (!empty($errors)) {
        echo "<ul style='color:red'>";
        foreach ($errors as $e) echo "<li>$e</li>";
        echo "</ul>";
    }

    if ($success) {
        echo "<p style='color:green;'>$success</p>";
    }
    ?>

    <form method="post" enctype="multipart/form-data">
        <label>Note:</label><br>
        <textarea name="note" required class="customeInput"></textarea><br><br>

        <label>Delivery Address:</label><br>
        <input type="text" name="delivery_address" class="customeInput" required><br><br>

        <label>Delivery Time Slot:</label><br>
        <select name="delivery_time" class="customeInput" required>
            <option value="">Select a time slot</option>
            <option value="08:00 - 10:00">08:00 - 10:00</option>
            <option value="10:00 - 12:00">10:00 - 12:00</option>
            <option value="12:00 - 14:00">12:00 - 14:00</option>
            <option value="14:00 - 16:00">14:00 - 16:00</option>
            <option value="16:00 - 18:00">16:00 - 18:00</option>
        </select><br><br>

        <label>Prescription Files (Max 5):</label><br>
        <input type="file" name="prescriptions[]" multiple required accept=".jpg,.jpeg,.png,.pdf"><br><br>

        <button type="submit" class="btn fixedWidhBtn">Upload</button>
    </form>
    </div>
<!-- </body>
</html> -->
<?php include 'components/footer.php'; ?>

