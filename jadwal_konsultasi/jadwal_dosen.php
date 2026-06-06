<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'dosen') {
    header("Location: dashboard.php");
    exit;
}

$id_dosen_login = $_SESSION['id_user'];

if (isset($_GET['aksi']) && isset($_GET['id'])) {

    $id_jadwal = $_GET['id'];
    $aksi = $_GET['aksi'];

    $status_baru = ($aksi == 'setujui') ? "Disetujui" : (($aksi == 'tolak') ? "Ditolak" : "");

    if ($status_baru != "") {

        $query_update = "UPDATE jadwal 
                         SET status = '$status_baru' 
                         WHERE id_jadwal = '$id_jadwal' 
                         AND id_dosen = '$id_dosen_login'";

        if(mysqli_query($koneksi, $query_update)){

            $_SESSION['notif'] = [
                "icon" => "success",
                "title" => "Berhasil!",
                "text"  => "Status berhasil diubah menjadi $status_baru"
            ];

            header("Location: jadwal_dosen.php");
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

<title>Jadwal Dosen</title>

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

.brand{
    font-weight:700;
    color:#4f46e5;
}

/* HEADER */
.page-header{
    background:linear-gradient(135deg,#4f46e5,#7c3aed);
    color:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(79,70,229,.25);
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

/* BUTTON */
.btn-action{
    border-radius:12px;
}

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

        <a class="navbar-brand brand" href="dashboard.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            Sistem Konsultasi
        </a>

        <div class="ms-auto d-flex gap-2">

            <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-house"></i> Dashboard
            </a>

            <a href="logout.php" class="btn btn-danger btn-sm">
                Logout
            </a>

        </div>

    </div>

</nav>

<div class="container py-4">

<!-- HEADER -->
<div class="page-header mb-4">

    <h3 class="mb-1">
        <i class="bi bi-calendar-week me-2"></i>
        Jadwal Bimbingan Saya
    </h3>

    <p class="mb-0">
        Kelola pengajuan mahasiswa yang masuk ke Anda
    </p>

</div>

<!-- TABLE -->
<div class="card main-card">

    <div class="card-header bg-white border-0 p-3">
        <h5 class="mb-0">
            Daftar Pengajuan
        </h5>
    </div>

    <div class="table-responsive">

        <table class="table table-hover mb-0">

            <thead class="table-light">

                <tr>
                    <th>No</th>
                    <th>Mahasiswa</th>
                    <th>Tanggal</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

            </thead>

            <tbody>

            <?php
            $no = 1;

            $query = "SELECT jadwal.*, users.nama_mhs, users.npm
                      FROM jadwal
                      JOIN users ON jadwal.id_user = users.id_user
                      WHERE jadwal.id_dosen = '$id_dosen_login'
                      ORDER BY jadwal.tgl_konsultasi DESC";

            $result = mysqli_query($koneksi,$query);

            if(mysqli_num_rows($result) > 0):

                while($row = mysqli_fetch_assoc($result)):
            ?>

            <tr>

                <td><?= $no++ ?></td>

                <td>
                    <strong><?= $row['nama_mhs'] ?></strong>
                    <br>
                    <small class="text-muted"><?= $row['npm'] ?></small>
                </td>

                <td>
                    <?= date('d M Y', strtotime($row['tgl_konsultasi'])) ?>
                </td>

                <td>
                    <?= $row['keperluan'] ?>
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

                        <a href="?aksi=setujui&id=<?= $row['id_jadwal'] ?>"
                           class="btn btn-success btn-sm btn-setujui">
                            Setujui
                        </a>

                        <a href="?aksi=tolak&id=<?= $row['id_jadwal'] ?>"
                           class="btn btn-danger btn-sm btn-tolak">
                            Tolak
                        </a>

                    <?php else: ?>

                        <span class="text-muted">Diproses</span>

                    <?php endif; ?>

                </td>

            </tr>

            <?php endwhile; else: ?>

            <tr>
                <td colspan="6">
                    <div class="empty">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2">Belum ada pengajuan</p>
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

<script>

document.querySelectorAll('.btn-setujui').forEach(btn=>{
    btn.addEventListener('click',function(e){
        e.preventDefault();
        let url=this.href;

        Swal.fire({
            title:'Setujui?',
            icon:'question',
            showCancelButton:true,
            confirmButtonText:'Ya'
        }).then(res=>{
            if(res.isConfirmed) window.location=url;
        });
    });
});

document.querySelectorAll('.btn-tolak').forEach(btn=>{
    btn.addEventListener('click',function(e){
        e.preventDefault();
        let url=this.href;

        Swal.fire({
            title:'Tolak?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Ya Tolak'
        }).then(res=>{
            if(res.isConfirmed) window.location=url;
        });
    });
});

</script>

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
