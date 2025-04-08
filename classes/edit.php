<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

$id = $_GET['id'];
$class = $conn->query("SELECT * FROM classes WHERE id = $id")->fetch_assoc();
$departments = $conn->query("SELECT * FROM departments");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $department_id = $_POST['department_id'];
    $semester = $_POST['semester'];
    $section = $_POST['section'];

    $stmt = $conn->prepare("UPDATE classes SET name=?, department_id=?, semester=?, section=? WHERE id=?");
    $stmt->bind_param("sissi", $name, $department_id, $semester, $section, $id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<h2>Edit Class</h2>
<form method="post">
    <label>Class Name:</label><br>
    <input type="text" name="name" value="<?= $class['name'] ?>" required><br><br>

    <label>Department:</label><br>
    <select name="department_id" required>
        <?php while ($d = $departments->fetch_assoc()) {
            $sel = ($d['id'] == $class['department_id']) ? "selected" : "";
            echo "<option value='{$d['id']}' $sel>{$d['name']}</option>";
        } ?>
    </select><br><br>

    <label>Semester:</label><br>
    <input type="number" name="semester" value="<?= $class['semester'] ?>" min="1" max="10" required><br><br>

    <label>Section:</label><br>
    <input type="text" name="section" value="<?= $class['section'] ?>" maxlength="1" required><br><br>

    <button type="submit">Update</button>
</form>

<?php include('../includes/footer.php'); ?>