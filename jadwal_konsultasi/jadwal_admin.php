<?php
session_start();
require_once 'config/koneksi.php';

/* =====================================
   PROTEKSI ADMIN
===================================== */

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

/* =====================================
   PROSES SETUJUI / TOLAK
===================================== */

if (isset($_GET['aksi']) && isset($_GET['id'])) {

    $id_jadwal = (int) $_GET['id'];
    $aksi      = $_GET['aksi'];

    $status_baru = "";

    if ($aksi == "setujui") {
        $status_baru = "Disetujui";
    }

    if ($aksi == "tolak") {
        $status_baru = "Ditolak";
    }

    if ($status_baru != "") {

        $update = mysqli_query(
            $koneksi,
            "UPDATE jadwal
             SET status='$status_baru'
             WHERE id_jadwal='$id_jadwal'"
        );

        if ($update) {

            $_SESSION['notif_jadwal'] = [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => "Status berhasil diubah menjadi $status_baru"
            ];

            header("Location: jadwal_admin.php");
            exit;
        }
    }
}

/* =====================================
   SEARCH
===================================== */

$keyword = "";

if (isset($_GET['keyword'])) {

    $keyword = mysqli_real_escape_string(
        $koneksi,
        trim($_GET['keyword'])
    );
}

/* =====================================
   FILTER STATUS
===================================== */

$status_filter = "";

if (isset($_GET['status'])) {

    $status_filter = mysqli_real_escape_string(
        $koneksi,
        trim($_GET['status'])
    );
}

/* =====================================
   STATISTIK
===================================== */

$total_jadwal = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM jadwal"
    )
);

$total_pending = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM jadwal
         WHERE status='Menunggu'"
    )
);

$total_setuju = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM jadwal
         WHERE status='Disetujui'"
    )
);

$total_tolak = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM jadwal
         WHERE status='Ditolak'"
    )
);

/* =====================================
   QUERY DATA TABEL
===================================== */

$where = [];

if ($keyword != "") {

    $where[] = "
    (
        users.nama_mhs LIKE '%$keyword%'
        OR users.npm LIKE '%$keyword%'
        OR dosen.nama_dosen LIKE '%$keyword%'
        OR jadwal.keperluan LIKE '%$keyword%'
    )";
}

if ($status_filter != "") {

    $where[] = "
    jadwal.status='$status_filter'
    ";
}

$where_sql = "";

if (count($where) > 0) {

    $where_sql = "WHERE " . implode(" AND ", $where);
}

$query = "

SELECT

jadwal.*,

users.nama_mhs,
users.npm,

dosen.nama_dosen

FROM jadwal

JOIN users
ON jadwal.id_user = users.id_user

JOIN dosen
ON jadwal.id_dosen = dosen.id_dosen

$where_sql

ORDER BY jadwal.id_jadwal DESC

";

$result = mysqli_query($koneksi, $query);

/* =====================================
   ADMIN LOGIN
===================================== */

$nama_admin = $_SESSION['nama_mhs'];

/* =====================================
   WAKTU
===================================== */

date_default_timezone_set('Asia/Jakarta');
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
Kelola Jadwal Konsultasi
</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>

:root{

    --primary:#4f46e5;
    --secondary:#7c3aed;

    --success:#10b981;
    --danger:#ef4444;
    --warning:#f59e0b;

    --dark:#0f172a;
    --light:#f8fafc;

}

/* =====================
   GLOBAL
===================== */

body{

    background:#f1f5f9;

    font-family:
    "Segoe UI",
    sans-serif;

}

a{
    text-decoration:none;
}

.container-custom{
    max-width:1400px;
}

/* =====================
   NAVBAR
===================== */

.navbar-custom{

    background:
    rgba(255,255,255,.95);

    backdrop-filter:
    blur(10px);

    box-shadow:
    0 4px 20px rgba(0,0,0,.05);

}

.brand-logo{

    width:42px;
    height:42px;

    border-radius:12px;

    background:
    linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;

}

.brand-title{

    font-weight:700;
    color:var(--dark);

}

/* =====================
   HERO
===================== */

.hero-banner{

    background:
    linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );

    border-radius:30px;

    color:white;

    padding:45px;

    position:relative;

    overflow:hidden;

    box-shadow:
    0 15px 40px rgba(79,70,229,.22);

}

.hero-banner::before{

    content:"";

    position:absolute;

    width:260px;
    height:260px;

    border-radius:50%;

    background:
    rgba(255,255,255,.08);

    right:-80px;
    top:-80px;

}

.hero-banner::after{

    content:"";

    position:absolute;

    width:200px;
    height:200px;

    border-radius:50%;

    background:
    rgba(255,255,255,.08);

    left:-60px;
    bottom:-60px;

}

.hero-banner h2{

    font-weight:700;

    position:relative;
    z-index:2;

}

.hero-banner p{

    position:relative;
    z-index:2;

}

/* =====================
   CLOCK
===================== */

.clock-box{

    background:white;

    border-radius:15px;

    padding:10px 18px;

    font-weight:600;

    box-shadow:
    0 4px 15px rgba(0,0,0,.05);

}

/* =====================
   STATS
===================== */

.stats-card{

    border:none;

    border-radius:24px;

    background:white;

    box-shadow:
    0 10px 25px rgba(0,0,0,.05);

    transition:.3s;

}

.stats-card:hover{

    transform:
    translateY(-6px);

}

.stats-icon{

    width:65px;
    height:65px;

    border-radius:18px;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;

    font-size:25px;

}

.icon-primary{

    background:
    linear-gradient(
        135deg,
        #6366f1,
        #8b5cf6
    );

}

.icon-warning{

    background:
    linear-gradient(
        135deg,
        #f59e0b,
        #fbbf24
    );

}

.icon-success{

    background:
    linear-gradient(
        135deg,
        #10b981,
        #34d399
    );

}

.icon-danger{

    background:
    linear-gradient(
        135deg,
        #ef4444,
        #f87171
    );

}

/* =====================
   SEARCH CARD
===================== */

.search-card{

    border:none;

    border-radius:24px;

    background:white;

    box-shadow:
    0 8px 20px rgba(0,0,0,.05);

}

.form-control,
.form-select{

    height:52px;

    border-radius:14px;

}

.form-control:focus,
.form-select:focus{

    border-color:#6366f1;

    box-shadow:
    0 0 0 4px
    rgba(99,102,241,.15);

}

/* =====================
   TABLE CARD
===================== */

.table-card{

    border:none;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
    0 10px 25px rgba(0,0,0,.05);

}

.table thead th{

    background:#f8fafc;

    text-transform:uppercase;

    font-size:12px;

    letter-spacing:.5px;

    color:#64748b;

    font-weight:700;

    text-align:center;

}

.table th,
.table td{

    padding:16px 18px;

    vertical-align:middle;

}

.table tbody tr:hover{

    background:#f8fafc;

}

.number-badge{

    width:38px;
    height:38px;

    border-radius:50%;

    background:#e2e8f0;

    display:flex;
    align-items:center;
    justify-content:center;

    font-weight:700;

    margin:auto;

}



.status{

    padding:8px 16px;

    border-radius:50px;

    font-size:12px;

    font-weight:700;

}

.status-pending{

    background:#fef3c7;
    color:#92400e;

}

.status-success{

    background:#dcfce7;
    color:#166534;

}

.status-danger{

    background:#fee2e2;
    color:#991b1b;

}

.btn-action{

    width:38px;
    height:38px;

    border-radius:12px;

    display:inline-flex;

    align-items:center;
    justify-content:center;

}

.btn-success-soft{

    background:#dcfce7;
    color:#166534;

    border:none;

}

.btn-danger-soft{

    background:#fee2e2;
    color:#991b1b;

    border:none;

}

.btn-success-soft:hover{

    background:#10b981;
    color:white;

}

.btn-danger-soft:hover{

    background:#ef4444;
    color:white;

}


.empty-state{

    padding:60px 20px;

    text-align:center;

    color:#94a3b8;

}

.empty-state i{

    font-size:60px;

}


@media(max-width:768px){

    .hero-banner{

        padding:30px;

    }

    .hero-banner h2{

        font-size:24px;

    }

    .table th,
    .table td{

        white-space:nowrap;

    }

}

</style>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">

    <div class="container container-custom">

        <a class="navbar-brand d-flex align-items-center gap-2" href="#">

            <div class="brand-logo">
                <i class="bi bi-calendar-check-fill"></i>
            </div>

            <span class="brand-title">
                Jadwal Konsultasi
            </span>

        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <div class="clock-box d-none d-md-block" id="clock"></div>

            <div class="text-end d-none d-md-block">

                <small class="text-muted">Admin</small><br>

                <strong><?= htmlspecialchars($nama_admin); ?></strong>

            </div>

            <a href="dashboard.php" class="btn btn-light btn-sm">
                Dashboard
            </a>

        </div>

    </div>

</nav>

<!-- HERO -->
<div class="container container-custom mt-4">

    <div class="hero-banner animate__animated animate__fadeInDown">

        <h2>
            Kelola Jadwal Konsultasi
        </h2>

        <p class="mb-0">
            Pantau, setujui, atau tolak pengajuan mahasiswa secara cepat dan terstruktur.
        </p>

    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mt-4">

        <!-- TOTAL -->
        <div class="col-6 col-lg-3">

            <div class="card stats-card p-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="stats-icon icon-primary">
                        <i class="bi bi-collection"></i>
                    </div>

                    <div>

                        <h5 class="mb-0">
                            <?= $total_jadwal ?>
                        </h5>

                        <small class="text-muted">
                            Total
                        </small>

                    </div>

                </div>

            </div>

        </div>

        <!-- PENDING -->
        <div class="col-6 col-lg-3">

            <div class="card stats-card p-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="stats-icon icon-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>

                    <div>

                        <h5 class="mb-0">
                            <?= $total_pending ?>
                        </h5>

                        <small class="text-muted">
                            Menunggu
                        </small>

                    </div>

                </div>

            </div>

        </div>

        <!-- SETUJUI -->
        <div class="col-6 col-lg-3">

            <div class="card stats-card p-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="stats-icon icon-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div>

                        <h5 class="mb-0">
                            <?= $total_setuju ?>
                        </h5>

                        <small class="text-muted">
                            Disetujui
                        </small>

                    </div>

                </div>

            </div>

        </div>

        <!-- TOLAK -->
        <div class="col-6 col-lg-3">

            <div class="card stats-card p-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="stats-icon icon-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>

                    <div>

                        <h5 class="mb-0">
                            <?= $total_tolak ?>
                        </h5>

                        <small class="text-muted">
                            Ditolak
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- SEARCH & FILTER -->
<div class="row mt-4">

    <div class="col-12">

        <div class="card search-card p-3">

            <form method="GET">

                <div class="row g-3 align-items-center">

                    <!-- SEARCH -->
                    <div class="col-lg-6">

                        <input type="text"
                               name="keyword"
                               class="form-control"
                               placeholder="Cari mahasiswa, NPM, dosen, keperluan..."
                               value="<?= htmlspecialchars($keyword) ?>">

                    </div>

                    <!-- FILTER -->
                    <div class="col-lg-3">

                        <select name="status" class="form-select">

                            <option value="">Semua Status</option>

                            <option value="Menunggu"
                            <?= $status_filter == "Menunggu" ? "selected" : "" ?>>
                                Menunggu
                            </option>

                            <option value="Disetujui"
                            <?= $status_filter == "Disetujui" ? "selected" : "" ?>>
                                Disetujui
                            </option>

                            <option value="Ditolak"
                            <?= $status_filter == "Ditolak" ? "selected" : "" ?>>
                                Ditolak
                            </option>

                        </select>

                    </div>

                    <!-- BUTTON -->
                    <div class="col-lg-3 d-grid">

                        <button class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- TABLE -->
<div class="row mt-4">

    <div class="col-12">

        <div class="card table-card">

            <div class="card-header bg-white border-0 p-3">

                <h5 class="mb-0">
                    Daftar Pengajuan
                </h5>

            </div>

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>

                        <tr>

                            <th style="width:70px;">No</th>

                            <th>Mahasiswa</th>

                            <th>Dosen</th>

                            <th>Tanggal</th>

                            <th>Keperluan</th>

                            <th>Status</th>

                            <th style="width:140px;">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if(mysqli_num_rows($result) > 0): ?>

                        <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <!-- NO -->
                            <td class="text-center">

                                <div class="number-badge">
                                    <?= $no++ ?>
                                </div>

                            </td>

                            <!-- MAHASISWA -->
                            <td>

                                <strong>
                                    <?= htmlspecialchars($row['nama_mhs']) ?>
                                </strong>

                                <br>

                                <small class="text-muted">
                                    <?= htmlspecialchars($row['npm']) ?>
                                </small>

                            </td>

                            <!-- DOSEN -->
                            <td>
                                <?= htmlspecialchars($row['nama_dosen']) ?>
                            </td>

                            <!-- TANGGAL -->
                            <td>
                                <?= date('d M Y', strtotime($row['tgl_konsultasi'])) ?>
                            </td>

                            <!-- KEPERLUAN -->
                            <td>
                                <?= htmlspecialchars($row['keperluan']) ?>
                            </td>

                            <!-- STATUS -->
                            <td>

                                <?php if($row['status'] == 'Menunggu'): ?>

                                    <span class="status status-pending">
                                        Menunggu
                                    </span>

                                <?php elseif($row['status'] == 'Disetujui'): ?>

                                    <span class="status status-success">
                                        Disetujui
                                    </span>

                                <?php else: ?>

                                    <span class="status status-danger">
                                        Ditolak
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- AKSI -->
                            <td class="text-center">

                                <?php if($row['status'] == 'Menunggu'): ?>

                                    <a href="?aksi=setujui&id=<?= $row['id_jadwal'] ?>"
                                       class="btn btn-action btn-success-soft btn-setujui">

                                        <i class="bi bi-check-lg"></i>

                                    </a>

                                    <a href="?aksi=tolak&id=<?= $row['id_jadwal'] ?>"
                                       class="btn btn-action btn-danger-soft btn-tolak">

                                        <i class="bi bi-x-lg"></i>

                                    </a>

                                <?php else: ?>

                                    <small class="text-muted">
                                        Diproses
                                    </small>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="7">

                                <div class="empty-state">

                                    <i class="bi bi-inbox"></i>

                                    <h5 class="mt-3">
                                        Tidak ada data
                                    </h5>

                                    <p class="mb-0">
                                        Belum ada pengajuan jadwal konsultasi
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CLOCK -->
<script>
function updateClock() {

    const now = new Date();

    document.getElementById("clock").innerHTML =
        now.toLocaleDateString("id-ID", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        }) + " | " +
        now.toLocaleTimeString("id-ID");

}

setInterval(updateClock, 1000);
updateClock();
</script>

<script>

document.querySelectorAll('.btn-setujui').forEach(btn => {

    btn.addEventListener('click', function(e){

        e.preventDefault();

        const url = this.getAttribute('href');

        Swal.fire({

            title: 'Setujui pengajuan ini?',
            text: "Status akan berubah menjadi Disetujui",
            icon: 'question',

            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',

            confirmButtonColor: '#10b981'

        }).then((result) => {

            if(result.isConfirmed){
                window.location.href = url;
            }

        });

    });

});


document.querySelectorAll('.btn-tolak').forEach(btn => {

    btn.addEventListener('click', function(e){

        e.preventDefault();

        const url = this.getAttribute('href');

        Swal.fire({

            title: 'Tolak pengajuan ini?',
            text: "Status akan berubah menjadi Ditolak",
            icon: 'warning',

            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',

            confirmButtonColor: '#ef4444'

        }).then((result) => {

            if(result.isConfirmed){
                window.location.href = url;
            }

        });

    });

});

</script>

<?php if(isset($_SESSION['notif_jadwal'])): ?>

<script>

document.addEventListener('DOMContentLoaded', function(){

    Swal.fire({

        icon: '<?= $_SESSION['notif_jadwal']['icon'] ?>',
        title: '<?= $_SESSION['notif_jadwal']['title'] ?>',
        text: '<?= $_SESSION['notif_jadwal']['text'] ?>',

        timer: 2500,
        showConfirmButton: false

    });

});

</script>

<?php unset($_SESSION['notif_jadwal']); endif; ?>

</body>
</html>
