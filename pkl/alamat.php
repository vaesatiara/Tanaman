<?php
include "koneksi.php";
$sql = "SELECT * FROM pengiriman ";
$query=mysqli_query($koneksi,$sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<a href=tambah_alamat1.php><button>Tambah</button></a>
    <table border = 1>
        <tr>
    <td>Label Alamat</td>
    <td>Nama</td>
    <td>NO hp</td>
    <td>provisni</td>
    <td>kota</td>
    <td>kecamatan</td>
    <td>catatan</td>
    </tr>

<?php while($pengiriman=mysqli_fetch_assoc($query)){ ?>
    <tr>
        <td><?=$pengiriman['id_pengiriman']?></td>
        <td><?=$pengiriman['label_alamat']?></td>
        <td><?=$pengiriman['nama_penerima']?></td>
        <td><?=$pengiriman['no_telepon']?></td>
        <td><?=$pengiriman['provinsi']?></td>
        <td><?=$pengiriman['kota']?></td>
        <td><?=$pengiriman['kecamatan']?></td>
        <td><?=$pengiriman['alamat_lengkap']?></td>
    </tr>
</table>
</body>
</html>

<?php } ?>