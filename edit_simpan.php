<?php
include 'db.php';

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM simpan WHERE id=$id"));

if(isset($_POST['jumlah'])){
    mysqli_query($conn,"
        UPDATE simpan
        SET jumlah='".$_POST['jumlah']."'
        WHERE id=$id
    ");
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Simpanan</title>
    <link rel="stylesheet" href="edit.css">
</head>
<body>

<div class="box">
    <h2>Edit Simpanan</h2>

    <form method="post">
        <label>Jumlah (RM)</label>
        <input type="number" step="0.01" name="jumlah" value="<?= $data['jumlah'] ?>" required>

        <button>Kemaskini</button>
        <a href="index.php" class="back">Batal</a>
    </form>
</div>

</body>
</html>
