<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $roll_number = $_POST['roll_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO students (class_id, roll_number, first_name, last_name, email, phone)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $class_id, $roll_number, $first_name, $last_name, $email, $phone);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

$classes = $conn->query("SELECT * FROM classes");
?>

<h2>Add Student</h2>
<form method="post">
    <label>Roll Number:</label><br>
    <input type="text" name="roll_number" required><br><br>

    <label>First Name:</label><br>
    <input type="text" name="first_name" required><br><br>

    <label>Last Name:</label><br>
    <input type="text" name="last_name" required><br><br>

    <label>Class:</label><br>
    <select name="class_id" required>
        <?php while ($c = $classes->fetch_assoc()) {
            echo "<option value='{$c['id']}'>{$c['name']}</option>";
        } ?>
    </select><br><br>

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <label>Phone:</label><br>
    <input type="text" name="phone"><br><br>

    <button type="submit">Save</button>
</form>

<?php include('../includes/footer.php'); ?>