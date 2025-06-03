<?php
// Start session
session_start();
include 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $dob = $_POST['dob'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($address) || empty($contact) || empty($dob) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $contact)) {
        $errors[] = "Contact number must be numeric and 7-15 digits.";
    }

    if (empty($errors)) {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $errors[] = "Email already registered.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, address, contact_no, dob, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $address, $contact, $dob, $hashedPassword);

            if ($stmt->execute()) {
                $success = "Registration successful. You can now <a href='login.php'>login</a>.";
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body>
    <div class="login-container">
    <h2>Register</h2>

    <?php
    if (!empty($errors)) {
        echo "<ul style='color: red;'>";
        foreach ($errors as $e) echo "<li>$e</li>";
        echo "</ul>";
    }

    if ($success) {
        echo "<p style='color: green;'>$success</p>";
    }
    ?>

    <form method="post">
        <label>Full Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Address:</label><br>
        <input type="text" name="address" required><br><br>

        <label>Contact No:</label><br>
        <input type="text" name="contact" required><br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="dob" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
