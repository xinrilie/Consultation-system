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

    $query_hapus = "DELETE FROM jadwal 
                    WHERE id_jadwal = '$id_jadwal' 
                    AND id_user = '$id_user_login' 
                    AND status = 'Menunggu'";

    if (mysqli_query($koneksi, $query_hapus)) {

        $_SESSION['notif'] = [
            "icon" => "success",
            "title" => "Berhasil!",
            "text"  => "Jadwal berhasil dibatalkan"
        ];

        header("Location: jadwal_saya.php");
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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

/* NAVBAR */
.navbar-custom{
    background:white;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
}

/* HEADER */
.page-header{
    background:linear-gradient(135deg,#06b6d4,#3b82f6);
    color:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(59,130,246,.25);
}

/* CARD */
.main-card{
    border:none;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,.06);
}

/* STATUS */
.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.s-wait{background:#fef3c7;color:#92400e;}
.s-acc{background:#dcfce7;color:#166534;}
.s-reject{background:#fee2e2;color:#991b1b;}

/* EMPTY */
.empty{
    padding:40px;
    text-align:center;
    color:#64748b;
}

</style>
</head>

<body>

<!-- NAVBAR -->
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

<!-- HEADER -->
<div class="page-header mb-4">

    <h3 class="mb-1">
        <i class="bi bi-clock-history me-2"></i>
        Riwayat Jadwal Saya
    </h3>

    <p class="mb-0">
        Semua pengajuan konsultasi Anda
    </p>

</div>

<!-- TABLE -->
<div class="card main-card">

    <div class="card-header bg-white border-0 p-3">
        <h5 class="mb-0">Daftar Pengajuan</h5>
    </div>

    <div class="table-responsive">

        <table class="table table-hover mb-0">

            <thead class="table-light">

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

            $query = "SELECT jadwal.*, dosen.nama_dosen
                      FROM jadwal
                      JOIN dosen ON jadwal.id_dosen = dosen.id_dosen
                      WHERE jadwal.id_user = '$id_user_login'
                      ORDER BY jadwal.tgl_konsultasi DESC";

            $result = mysqli_query($koneksi,$query);

            if(mysqli_num_rows($result) > 0):

                while($row = mysqli_fetch_assoc($result)):
            ?>

            <tr>

                <td><?= $no++ ?></td>

                <td>
                    <?= date('d M Y', strtotime($row['tgl_konsultasi'])) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['nama_dosen']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['keperluan']) ?>
                </td>

                <td>

                    <?php if($row['status']=='Menunggu'): ?>
                        <span class="status s-wait">Menunggu</span>

                    <?php elseif($row['status']=='Disetujui'): ?>
                        <span class="status s-acc">Disetujui</span>

                    <?php else: ?>
                        <span class="status s-reject">Ditolak</span>
                    <?php endif; ?>

                </td>

                <td>

                    <?php if($row['status']=='Menunggu'): ?>

                        <a href="?batal=<?= $row['id_jadwal'] ?>"
                           class="btn btn-danger btn-sm btn-batal">
                            Batalkan
                        </a>

                    <?php else: ?>

                        <span class="text-muted">Selesai</span>

                    <?php endif; ?>

                </td>

            </tr>

            <?php endwhile; else: ?>

            <tr>
                <td colspan="6">
                    <div class="empty">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2 mb-0">Belum ada riwayat jadwal</p>
                    </div>
                </td>
            </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- KONFIRMASI BATAL -->
<script>
document.querySelectorAll('.btn-batal').forEach(btn=>{
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const url = this.href;

        Swal.fire({
            title:'Batalkan pengajuan?',
            text:'Data akan dihapus dari sistem',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Ya, batalkan',
            confirmButtonColor:'#dc2626'
        }).then(res=>{
            if(res.isConfirmed) window.location=url;
        });
    });
});
</script>

<!-- NOTIF -->
<?php if(isset($_SESSION['notif'])): ?>
<script>
Swal.fire({
    icon:'<?= $_SESSION['notif']['icon'] ?>',
    title:'<?= $_SESSION['notif']['title'] ?>',
    text:'<?= $_SESSION['notif']['text'] ?>',
    timer:2000,
    showConfirmButton:false
});
</script>
<?php unset($_SESSION['notif']); endif; ?>

</body>
</html>
