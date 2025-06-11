<?php
session_start();
include "koneksi.php";

// Clear cart in session
$_SESSION['keranjang'] = array();

// Also clear cart in database if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $koneksi->query("DELETE FROM keranjang WHERE id_user='$user_id'");
}

// Redirect back to cart page
header("Location: keranjang.php");
exit;
?>