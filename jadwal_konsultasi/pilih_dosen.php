<?php
session_start();
require_once 'config/koneksi.php';

// Proteksi akses: hanya mahasiswa
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Dosen Pembimbing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
         .navbar-custom{
    background:white;
    box-shadow:0 5px 20px rgba(0,0,0,.06);}
        .card-dosen { transition: transform 0.3s; }
        .card-dosen:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        /* Style foto profil bulat */
        .foto-profil {
            width: 120px;
            height: 120px;
            object-fit: cover;
            object-position: center;
            border: 4px solid #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-custom">

    <div class="container">

        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            Sistem Konsultasi
        </a>

        <div class="ms-auto">

            <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-house"></i> Dashboard
            </a>

        </div>

    </div>

</nav>

<div class="container">
    <div class="progress mb-4 shadow-sm" style="height: 25px; border-radius: 20px;">
        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated fw-bold" role="progressbar" style="width: 50%;">Tahap 1: Pilih Dosen</div>
    </div>

    <div class="text-center mb-5">
        <h3 class="fw-bold">Pilih Dosen Konsultasi</h3>
        <p class="text-muted">Silakan pilih dosen yang sesuai dengan topik bimbingan Anda.</p>
    </div>

    <div class="row justify-content-center">
        <?php
        $query_dosen = "SELECT * FROM dosen ORDER BY nama_dosen ASC";
        $result = mysqli_query($koneksi, $query_dosen);

        if(mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                $foto_dosen = (!empty($row['foto'])) ? $row['foto'] : 'default.png';
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
            <div class="card shadow-sm border-0 card-dosen h-100 pt-4">
                <div class="text-center">
                    <img src="uploads/<?= $foto_dosen; ?>" class="rounded-circle foto-profil" alt="Foto">
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title fw-bold text-primary mb-2"><?= $row['nama_dosen']; ?></h5>
                    <span class="badge bg-secondary mb-4 mt-auto"><?= $row['kategori_keahlian']; ?></span>
                    
                    <a href="atur_jadwal.php?id_dosen=<?= $row['id_dosen']; ?>" class="btn btn-primary w-100 fw-bold">Pilih Dosen</a>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else: 
        ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-warning">Belum ada data dosen yang terdaftar.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
