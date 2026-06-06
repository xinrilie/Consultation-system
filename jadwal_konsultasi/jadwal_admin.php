<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_jadwal = $_GET['id'];
    $aksi = $_GET['aksi'];
    
    $status_baru = ($aksi == 'setujui') ? "Disetujui" : (($aksi == 'tolak') ? "Ditolak" : "");

    if ($status_baru != "") {
        $query_update = "UPDATE jadwal SET status = '$status_baru' WHERE id_jadwal = '$id_jadwal'";
        if(mysqli_query($koneksi, $query_update)){
             echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
             echo "<script>
                 document.addEventListener('DOMContentLoaded', function() {
                     Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Status diubah menjadi $status_baru.', showConfirmButton: false, timer: 1500 }).then(() => {
                         window.location.href = 'jadwal_admin.php';
                     });
                 });
             </script>";
             exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Bimbingan</title>
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
        <div class="card-header bg-dark text-white"><h5 class="mb-0">Daftar Pengajuan Mahasiswa</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover m-0" style="min-width: 800px;">
                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Mahasiswa (NPM)</th>
                            <th>Dosen Tujuan</th>
                            <th>Tanggal</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = "SELECT jadwal.*, users.nama_mhs, users.npm, dosen.nama_dosen FROM jadwal JOIN users ON jadwal.id_user = users.id_user JOIN dosen ON jadwal.id_dosen = dosen.id_dosen ORDER BY jadwal.id_jadwal DESC";
                        $result = mysqli_query($koneksi, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= $row['nama_mhs']; ?></strong><br><small><?= $row['npm']; ?></small></td>
                            <td><?= $row['nama_dosen']; ?></td>
                            <td><?= date('d M Y', strtotime($row['tgl_konsultasi'])); ?></td>
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
                                    <a href="jadwal_admin.php?aksi=setujui&id=<?= $row['id_jadwal']; ?>" class="btn btn-success btn-sm mb-1 btn-setujui">Setujui</a>
                                    <a href="jadwal_admin.php?aksi=tolak&id=<?= $row['id_jadwal']; ?>" class="btn btn-danger btn-sm mb-1 btn-tolak">Tolak</a>
                                <?php else: ?>
                                    <span class="text-muted"><small>Diproses</small></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; } else { echo "<tr><td colspan='7' class='text-center'>Belum ada data.</td></tr>"; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Konfirmasi Setujui
    document.querySelectorAll('.btn-setujui').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({
                title: 'Setujui Pengajuan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => { if (result.isConfirmed) { window.location.href = url; } })
        });
    });

    // Konfirmasi Tolak
    document.querySelectorAll('.btn-tolak').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({
                title: 'Tolak Pengajuan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => { if (result.isConfirmed) { window.location.href = url; } })
        });
    });
</script>
</body>
</html>