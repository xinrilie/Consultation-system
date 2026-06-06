<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$nama_user = $_SESSION['nama_mhs'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Konsultasi</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="menuNavbar">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" id="profilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Halo, <strong><?= $nama_user; ?></strong> (<?= ucfirst($role); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="profilDropdown">
                        
                        <?php if ($role == 'dosen'): ?>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="profil_dosen.php">
                                ⚙️ Edit Profil & Foto
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li>
                            <a class="dropdown-item text-danger fw-bold d-flex align-items-center gap-2 btn-logout" href="logout.php">
                                🚪 Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <?php if ($role == 'admin'): ?>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h5 class="card-title fw-bold text-primary">Data Dosen</h5>
                        <p class="text-muted">Kelola master data dosen (Tambah, Edit, Hapus).</p>
                        <a href="dosen.php" class="btn btn-primary w-100 mt-auto">Kelola Dosen</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h5 class="card-title fw-bold text-dark">Semua Jadwal Bimbingan</h5>
                        <p class="text-muted">Pantau seluruh pengajuan bimbingan di sistem.</p>
                        <a href="jadwal_admin.php" class="btn btn-dark w-100 mt-auto">Lihat Semua Jadwal</a>
                    </div>
                </div>
            </div>

        <?php elseif ($role == 'dosen'): ?>
            <div class="col-md-6 mx-auto mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h5 class="card-title fw-bold text-success">Jadwal Bimbingan Saya</h5>
                        <p class="text-muted">Setujui atau tolak pengajuan konsultasi dari mahasiswa.</p>
                        <a href="jadwal_dosen.php" class="btn btn-success w-100 mt-auto">Kelola Jadwal Saya</a>
                    </div>
                </div>
            </div>

        <?php elseif ($role == 'mahasiswa'): ?>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h5 class="card-title fw-bold text-primary">Buat Pengajuan</h5>
                        <p class="text-muted">Pilih dosen dan ajukan jadwal bimbingan baru.</p>
                        <a href="pilih_dosen.php" class="btn btn-primary w-100 mt-auto">Buat Pengajuan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h5 class="card-title fw-bold text-info">Riwayat Saya</h5>
                        <p class="text-muted">Lihat status pengajuan yang telah Anda buat.</p>
                        <a href="jadwal_saya.php" class="btn btn-info text-white w-100 mt-auto">Lihat Jadwal</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert untuk konfirmasi Logout
    document.querySelectorAll('.btn-logout').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({ 
                title: 'Yakin ingin keluar?', 
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonColor: '#d33', 
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = url; }
            })
        });
    });
</script>
</body>
</html>