<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

$sql = "SELECT students.*, classes.name as class_name 
        FROM students 
        JOIN classes ON students.class_id = classes.id";
$result = $conn->query($sql);
?>

<h2>All Students</h2>
<a href="add.php">+ Add Student</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>Roll Number</th>
        <th>Name</th>
        <th>Class</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['roll_number'] ?></td>
        <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
        <td><?= $row['class_name'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['phone'] ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include('../includes/footer.php'); ?>