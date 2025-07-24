<!-- Update bagian checkout button di keranjang.php -->

<!-- Untuk checkout semua item di keranjang -->
<a href="alamat_pengiriman.php?source=cart" 
   class="checkout-btn <?php echo empty($_SESSION['keranjang']) ? 'disabled' : ''; ?>">
    Lanjutkan ke Pembayaran
</a>

<!-- Untuk checkout item individual dari keranjang -->
<a href="alamat_pengiriman.php?source=individual_checkout&id_produk=<?php echo $id_produk; ?>&qty=<?php echo $jumlah; ?>" 
   class="checkout-item-btn">
    <i class="fas fa-shopping-bag"></i> Checkout Produk Ini
</a>

<!-- Untuk buy now dari halaman produk -->
<a href="alamat_pengiriman.php?source=buy_now&id_produk=<?php echo $produk['id_produk']; ?>&qty=1" 
   class="buy-now-btn">
    <i class="fas fa-bolt"></i> Beli Sekarang
</a>