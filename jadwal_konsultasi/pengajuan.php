<?php
session_start();
require_once 'config/koneksi.php';

// Proteksi akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['simpan_jadwal'])) {
    $id_user = $_SESSION['id_user']; // ID Mahasiswa yang login
    $id_dosen = mysqli_real_escape_string($koneksi, $_POST['id_dosen']);
    $tgl = mysqli_real_escape_string($koneksi, $_POST['tgl_konsultasi']);
    $keperluan = mysqli_real_escape_string($koneksi, $_POST['keperluan']);
    
    // Default status selalu 'Menunggu' saat pertama kali diajukan
    $status = 'Menunggu';

    $query = "INSERT INTO jadwal (id_user, id_dosen, tgl_konsultasi, keperluan, status) 
              VALUES ('$id_user', '$id_dosen', '$tgl', '$keperluan', '$status')";
    
    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, tampilkan animasi sukses dan arahkan ke jadwal_saya.php
        echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Pengajuan Berhasil!',
                    text: 'Jadwal Anda sedang menunggu persetujuan dosen.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = 'jadwal_saya.php';
                });
            });
        </script></body></html>";
        exit;
    } else {
        // Jika error
        echo "Error: " . mysqli_error($koneksi);
    }
} else {
    // Jika ada yang akses file ini langsung dari URL tanpa isi form
    header("Location: pilih_dosen.php");
    exit;
}
?>