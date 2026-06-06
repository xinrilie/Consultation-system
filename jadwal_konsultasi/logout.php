<?php
session_start();
// Menghapus semua sesi yang berjalan
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar dari Sistem</title>
    <style>
        body { background-color: #f4f7f6; }
    </style>
</head>
<body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Memunculkan animasi setelah halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Keluar',
            text: 'Sesi Anda telah diakhiri. Sampai jumpa!',
            showConfirmButton: false,
            timer: 1500 // Muncul selama 1.5 detik
        }).then(() => {
            // Setelah animasi selesai, baru arahkan ke index.php
            window.location.href = 'index.php';
        });
    });
</script>

</body>
</html>