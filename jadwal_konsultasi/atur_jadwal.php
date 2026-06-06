<?php
session_start();
require_once 'config/koneksi.php';

// Proteksi akses: hanya mahasiswa
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: dashboard.php");
    exit;
}

// Cek apakah ada id_dosen yang dilempar dari URL
if (!isset($_GET['id_dosen'])) {
    header("Location: pilih_dosen.php");
    exit;
}

$id_dosen = mysqli_real_escape_string($koneksi, $_GET['id_dosen']);
$query_dosen = mysqli_query($koneksi, "SELECT * FROM dosen WHERE id_dosen = '$id_dosen'");
$dosen = mysqli_fetch_assoc($query_dosen);

// Jika dosen tidak ditemukan (misal ID asal ketik)
if (!$dosen) {
    echo "<script>alert('Dosen tidak valid!'); window.location.href='pilih_dosen.php';</script>";
    exit;
}

$foto_dosen = (!empty($dosen['foto'])) ? $dosen['foto'] : 'default.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Jadwal Konsultasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .navbar-custom{
    background:white;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
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

<div class="container py-4">

<div class="container">
    <div class="progress mb-4 shadow-sm" style="height: 25px; border-radius: 20px;">
        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated fw-bold" role="progressbar" style="width: 100%;">Tahap 2: Atur Tanggal & Keperluan</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted mb-3">Anda akan konsultasi dengan:</h6>
                <img src="uploads/<?= $foto_dosen; ?>" class="rounded-circle shadow-sm mx-auto mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <h5 class="fw-bold text-primary mb-1"><?= $dosen['nama_dosen']; ?></h5>
                <span class="badge bg-secondary"><?= $dosen['kategori_keahlian']; ?></span>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white fw-bold py-3">
                    Form Pengajuan Jadwal
                </div>
                <div class="card-body p-4">
                    <form action="pengajuan.php" method="POST">
                        <input type="hidden" name="id_dosen" value="<?= $dosen['id_dosen']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Tanggal Konsultasi</label>
                            <input type="date" name="tgl_konsultasi" class="form-control" required min="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Keperluan / Topik Bimbingan</label>
                            <textarea name="keperluan" class="form-control" rows="4" placeholder="Jelaskan secara singkat topik atau bab yang ingin dikonsultasikan..." required></textarea>
                        </div>
                        
                        <button type="submit" name="simpan_jadwal" class="btn btn-primary w-100 fw-bold py-2">Kirim Pengajuan Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
