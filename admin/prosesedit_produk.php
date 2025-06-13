<?php
include "koneksi.php";

// Basic error handling
if (!isset($_POST['id_produk'])) {
    die("Error: No product ID received");
}

// Get form data
$id_produk = $_POST['id_produk'];
$nama_tanaman = $_POST['nama_tanaman'];
$kategori = $_POST['kategori'];
$harga = $_POST['harga'];
$stok = $_POST['stok'];
$deskripsi = $_POST['deskripsi'];

// Get additional data
$keunggulan = isset($_POST['keunggulan']) ? $_POST['keunggulan'] : '';
$perawatan_harian = isset($_POST['perawatan_harian']) ? $_POST['perawatan_harian'] : '';
$perawatan_berkala = isset($_POST['perawatan_berkala']) ? $_POST['perawatan_berkala'] : '';

// Start building the SQL query
$sql = "UPDATE produk SET 
        nama_tanaman = '$nama_tanaman', 
        kategori = '$kategori', 
        harga = '$harga',
        stok = '$stok', 
        deskripsi = '$deskripsi',
        keunggulan = '$keunggulan',
        perawatan_harian = '$perawatan_harian',
        perawatan_berkala = '$perawatan_berkala'";

// Handle file upload if present
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && !empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    
    // Generate a unique filename to prevent overwriting
    $extension = pathinfo($foto, PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '.' . $extension;
    
    if (move_uploaded_file($tmp, "uploads/" . $unique_filename)) {
        $sql .= ", foto = '$unique_filename'";
        
        // Delete old image if exists
        if (isset($_POST['foto_lama']) && !empty($_POST['foto_lama'])) {
            $old_file = "uploads/" . $_POST['foto_lama'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
    }
}

// Complete the SQL query
$sql .= " WHERE id_produk = '$id_produk'";

// Execute the query
$query = mysqli_query($koneksi, $sql);

if ($query) {
    header("Location: manajemen_produk.php?edit=sukses");
    exit;
} else {
    echo "Error updating record: " . mysqli_error($koneksi);
    echo "<br>SQL Query: $sql";
    echo "<br><a href='manajemen_produk.php'>Return to product management</a>";
    exit;
}
?>
