<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

$id = $_GET['id'];
$student = $conn->query("SELECT * FROM students WHERE id = $id")->fetch_assoc();
$classes = $conn->query("SELECT * FROM classes");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE students SET class_id=?, first_name=?, last_name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("issssi", $class_id, $first_name, $last_name, $email, $phone, $id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<h2>Edit Student</h2>
<form method="post">
    <label>Roll Number:</label><br>
    <input type="text" value="<?= $student['roll_number'] ?>" disabled><br><br>

    <label>First Name:</label><br>
    <input type="text" name="first_name" value="<?= $student['first_name'] ?>" required><br><br>

    <label>Last Name:</label><br>
    <input type="text" name="last_name" value="<?= $student['last_name'] ?>" required><br><br>

    <label>Class:</label><br>
    <select name="class_id" required>
        <?php while ($c = $classes->fetch_assoc()) {
            $sel = ($c['id'] == $student['class_id']) ? "selected" : "";
            echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
        } ?>
    </select><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= $student['email'] ?>"><br><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" value="<?= $student['phone'] ?>"><br><br>

    <button type="submit">Update</button>
</form>

<?php include('../includes/footer.php'); ?>