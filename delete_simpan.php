<?php
include 'db.php';

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

mysqli_query($conn,"DELETE FROM simpan WHERE id=$id");

header("Location: index.php");
exit;
