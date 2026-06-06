<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: dashboard.php");
    exit;
}

$id_user_login = $_SESSION['id_user'];

if (isset($_GET['batal'])) {
    $id_jadwal = $_GET['batal'];
    $query_hapus = "DELETE FROM jadwal WHERE id_jadwal = '$id_jadwal' AND id_user = '$id_user_login' AND status = 'Menunggu'";
    if (mysqli_query($koneksi, $query_hapus)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'Dibatalkan!', text: 'Jadwal berhasil dibatalkan.', showConfirmButton: false, timer: 1500 }).then(() => {
                    window.location.href = 'jadwal_saya.php';
                });
            });
        </script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Jadwal Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Sistem Konsultasi</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-white" href="dashboard.php">&larr; Kembali ke Dashboard</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white"><h5 class="mb-0">Riwayat Pengajuan Saya</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover m-0" style="min-width: 600px;">
                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Dosen</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = "SELECT jadwal.*, dosen.nama_dosen FROM jadwal JOIN dosen ON jadwal.id_dosen = dosen.id_dosen WHERE jadwal.id_user = '$id_user_login' ORDER BY jadwal.tgl_konsultasi DESC";
                        $result = mysqli_query($koneksi, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d M Y', strtotime($row['tgl_konsultasi'])); ?></td>
                            <td><?= $row['nama_dosen']; ?></td>
                            <td><?= $row['keperluan']; ?></td>
                            <td>
                                <?php 
                                    if ($row['status'] == 'Menunggu') echo "<span class='badge bg-warning text-dark'>Menunggu</span>";
                                    elseif ($row['status'] == 'Disetujui') echo "<span class='badge bg-success'>Disetujui</span>";
                                    else echo "<span class='badge bg-danger'>Ditolak</span>";
                                ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'Menunggu'): ?>
                                    <a href="jadwal_saya.php?batal=<?= $row['id_jadwal']; ?>" class="btn btn-danger btn-sm btn-batal">Batalkan</a>
                                <?php else: ?>
                                    <span class="text-muted"><small>Selesai</small></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; } else { echo "<tr><td colspan='6' class='text-center'>Belum ada riwayat.</td></tr>"; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.btn-batal').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: "Anda akan membatalkan jadwal ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = url; }
            })
        });
    });
</script>
</body>
</html>