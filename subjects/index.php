<?php
include('../includes/auth.php');
include('../includes/header.php');
include('../config/db.php');

$sql = "SELECT subjects.*, classes.name AS class_name 
        FROM subjects 
        JOIN classes ON subjects.class_id = classes.id";
$result = $conn->query($sql);
?>

<h2>All Subjects</h2>
<a href="add.php">+ Add Subject</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>Subject Code</th>
        <th>Subject Name</th>
        <th>Class</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['subject_code'] ?></td>
        <td><?= $row['name'] ?></td>
        <td><?= $row['class_name'] ?></td>
        <td>
            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this subject?')">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include('../includes/footer.php'); ?>