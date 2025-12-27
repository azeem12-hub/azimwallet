<?php
include 'db.php';
if(isset($_POST['delete_kotak'])){
    $id = $_POST['delete_kotak'];

    mysqli_query($conn,"DELETE FROM simpan WHERE kotak_id=$id");
    mysqli_query($conn,"DELETE FROM kotak WHERE id=$id");

    header("Location: index.php");
    exit;
}
$where = "";
if(!empty($_GET['bulan'])){
    $where = "WHERE DATE_FORMAT(tarikh,'%Y-%m') = '".$_GET['bulan']."'";
}

$bulanan = mysqli_query($conn,"
SELECT 
    DATE_FORMAT(tarikh, '%M %Y') AS bulan,
    SUM(jumlah) AS total
FROM simpan
GROUP BY YEAR(tarikh), MONTH(tarikh)
ORDER BY tarikh DESC
");

$totalBulan = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT IFNULL(SUM(jumlah),0) AS total
    FROM simpan
    ".(!empty($where)?$where:"")
    )
)['total'];

$dataKotak = mysqli_query($conn,"
SELECT 
    k.id, 
    k.nama, 
    IFNULL(SUM(s.jumlah),0) AS total
FROM kotak k
LEFT JOIN simpan s 
    ON k.id = s.kotak_id
    ".(!empty($_GET['bulan']) ? "AND DATE_FORMAT(s.tarikh,'%Y-%m') = '".$_GET['bulan']."'" : "")."
GROUP BY k.id
");

$bulan = $_GET['bulan'] ?? date('Y-m');

mysqli_query($conn,"
INSERT IGNORE INTO gaji (bulan, jumlah)
VALUES ('$bulan', 0)
");

$qGaji = mysqli_query($conn,"
    SELECT jumlah
    FROM gaji
    WHERE bulan='$bulan'
");

$rowGaji = mysqli_fetch_assoc($qGaji);
$gaji = $rowGaji ? $rowGaji['jumlah'] : 0;
$simpan = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT IFNULL(SUM(jumlah),0) AS total
        FROM simpan
        WHERE DATE_FORMAT(tarikh,'%Y-%m')='$bulan'
    ")
)['total'];

$dataSimpan = mysqli_query($conn,"
SELECT s.id, k.nama, s.jumlah, s.tarikh
FROM simpan s
JOIN kotak k ON k.id=s.kotak_id
ORDER BY s.tarikh DESC
");

if(isset($_POST['nama_kotak'])){
    mysqli_query($conn,"
        INSERT INTO kotak(nama)
        VALUES('".$_POST['nama_kotak']."')
    ");
    header("Location: index.php");
    exit;
}


if(isset($_POST['gaji_baru'])){
    mysqli_query($conn,"
        UPDATE gaji
        SET jumlah='".$_POST['gaji_baru']."'
        WHERE bulan='$bulan'
    ");
    header("Location: index.php?bulan=$bulan");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Simpanan Aku</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<h1>Duit Simpanan Azim </h1>
<form method="get">
    <select name="bulan">
        <option value="">Semua Bulan</option>
        <?php
        $bulanQ = mysqli_query($conn,"
            SELECT DISTINCT DATE_FORMAT(tarikh,'%Y-%m') AS bulan
            FROM simpan
            ORDER BY bulan DESC
        ");
        while($b = mysqli_fetch_assoc($bulanQ)){
            $selected = (isset($_GET['bulan']) && $_GET['bulan']==$b['bulan']) ? 'selected' : '';
            echo "<option value='$b[bulan]' $selected>$b[bulan]</option>";
        }
        ?>
    </select>
    <button type="submit">Tapis</button>
    
</form><hr>

<form method="post">
    <input type="number" step="0.01" name="gaji_baru" value="<?= $gaji ?>">
    <button>Kemaskini Gaji</button>
</form>

<h3>Gaji Bulan Ini: RM <?= number_format($gaji,2) ?></h3>
<h3>Jumlah Simpanan: RM <?= number_format($simpan,2) ?></h3>
<h2>Baki Gaji: RM <?= number_format($gaji - $simpan,2) ?></h2>
<hr>



<div class="grid">
<?php while($row = mysqli_fetch_assoc($dataKotak)){ ?>

<div class="card">
    <h2><?= $row['nama'] ?></h2>
    <p class="big">RM <?= number_format($row['total'],2) ?></p>

    <form method="post" onsubmit="return confirm('Padam kotak ini dan semua simpanannya?')">
        <input type="hidden" name="delete_kotak" value="<?= $row['id'] ?>">
        <button class="btn-delete">Delete</button>
    </form>
</div>

<?php } ?>


</div>

<form method="post" class="tambah-kotak">
    <input type="text" name="nama_kotak" placeholder="Nama kotak" required>
    <button type="submit">+ Tambah Kotak</button>
</form>

<form action="simpan.php" method="post">
    <select name="kotak_id" required>
        <option value="">Pilih </option>
        <?php
        $k = mysqli_query($conn,"SELECT * FROM kotak");
        while($r = mysqli_fetch_assoc($k)){
            echo "<option value='$r[id]'>$r[nama]</option>";
        }
        ?>
    </select>

    <input type="number" step="0.01" name="jumlah" placeholder="Jumlah RM" required>
    <button>Simpan</button>
</form>

<hr>


<h3>Jumlah Simpanan Bulanan</h3>

<table border="0" cellpadding="10">
<?php while($b = mysqli_fetch_assoc($bulanan)){ ?>
<tr>
    <td><?= $b['bulan'] ?></td>
    <td><strong>RM <?= number_format($b['total'],2) ?></strong></td>
</tr>
<?php } ?>
</table>

<table>
<?php while($r = mysqli_fetch_assoc($dataSimpan)){ ?>
<tr>
  <td><?= $r['nama'] ?></td>
  <td>RM <?= number_format($r['jumlah'],2) ?></td>
  <td>
    <a href="edit_simpan.php?id=<?= $r['id'] ?>">Edit</a> |
    <a href="delete_simpan.php?id=<?= $r['id'] ?>"
       onclick="return confirm('Padam rekod ini?')">
       Delete
    </a>
  </td>
</tr>
<?php } ?>
</table>


</body>
</html>
