<?php
include 'db.php';

$kotak = $_POST['kotak_id'];
$catatan = $_POST['catatan'];
$tarikh = date('Y-m-d');
$jumlah = $_POST['jumlah'];


mysqli_query($conn,"
INSERT INTO simpan (kotak_id, tarikh, catatan, jumlah)
VALUES ('$kotak', '$tarikh', '$catatan', '$jumlah')
");


header("Location: index.php");
?>
