<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$nama_user = $_SESSION['nama_mhs'];
$role = $_SESSION['role'];

// Statistik Dashboard

$total_dosen = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM dosen"
    )
);

$total_mahasiswa = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM users
         WHERE role='mahasiswa'"
    )
);

$total_jadwal = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM jadwal"
    )
);

$total_menunggu = mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM jadwal
         WHERE status='pending'"
    )
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard - Sistem Konsultasi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

:root{
    --primary:#4f46e5;
    --secondary:#7c3aed;
    --success:#10b981;
    --info:#06b6d4;
    --dark:#0f172a;
    --light:#f8fafc;
}

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

/* =======================
   NAVBAR
======================= */

.navbar-custom{
    background:rgba(255,255,255,.95);
    backdrop-filter:blur(10px);
    box-shadow:0 3px 20px rgba(0,0,0,.05);
}

.brand-logo{
    width:42px;
    height:42px;
    border-radius:12px;
    background:linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
}
.clock-box{

    background:white;

    border-radius:15px;

    padding:10px 18px;

    font-weight:600;

    box-shadow:
    0 4px 15px rgba(0,0,0,.05);

}
.brand-text{
    font-weight:700;
    color:var(--dark);
}

/* =======================
   HERO
======================= */

.hero-banner{
    background:linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );

    border-radius:28px;

    padding:40px;

    color:white;

    overflow:hidden;

    position:relative;

    box-shadow:
    0 15px 40px rgba(79,70,229,.25);
}

.hero-banner::before{
    content:"";
    position:absolute;
    right:-50px;
    top:-50px;

    width:220px;
    height:220px;

    border-radius:50%;

    background:rgba(255,255,255,.08);
}

.hero-banner::after{
    content:"";
    position:absolute;
    right:40px;
    bottom:-70px;

    width:180px;
    height:180px;

    border-radius:50%;

    background:rgba(255,255,255,.08);
}

.hero-banner h2{
    font-weight:700;
}
/* ======================
   STATS CARD
====================== */

.stats-card{

    border:none;

    border-radius:24px;

    overflow:hidden;

    background:white;

    box-shadow:
    0 10px 25px rgba(0,0,0,.06);

    transition:.3s;
}

.stats-card:hover{

    transform:translateY(-5px);

    box-shadow:
    0 20px 35px rgba(79,70,229,.12);
}

.stats-icon{

    width:70px;
    height:70px;

    border-radius:20px;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;

    font-size:28px;
}

.bg-purple{
    background:linear-gradient(
        135deg,
        #6366f1,
        #8b5cf6
    );
}

.bg-green{
    background:linear-gradient(
        135deg,
        #10b981,
        #34d399
    );
}

.bg-blue{
    background:linear-gradient(
        135deg,
        #06b6d4,
        #3b82f6
    );
}

.bg-orange{
    background:linear-gradient(
        135deg,
        #f59e0b,
        #f97316
    );
}
/* =======================
   INFO BOX
======================= */

.info-card{
    border:none;
    border-radius:22px;
    background:white;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}

.info-icon{
    width:60px;
    height:60px;
    border-radius:18px;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;
    font-size:24px;

    background:linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );
}

/* =======================
   DASHBOARD CARD
======================= */

.dashboard-card{
    border:none;
    border-radius:25px;
    overflow:hidden;

    background:white;

    box-shadow:
    0 10px 25px rgba(0,0,0,.06);

    transition:.35s;
}

.dashboard-card:hover{
    transform:translateY(-8px);

    box-shadow:
    0 20px 40px rgba(79,70,229,.15);
}

.card-icon{
    width:90px;
    height:90px;

    border-radius:25px;

    margin:auto;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;
    font-size:38px;
}

.icon-primary{
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
}

.icon-success{
    background:linear-gradient(135deg,#10b981,#34d399);
}

.icon-info{
    background:linear-gradient(135deg,#06b6d4,#3b82f6);
}

.icon-dark{
    background:linear-gradient(135deg,#334155,#0f172a);
}

.dashboard-card .btn{
    border-radius:14px;
    font-weight:600;
}

/* =======================
   OFFCANVAS MENU
======================= */

.menu-link{
    display:flex;
    align-items:center;
    gap:12px;

    padding:12px 15px;

    border-radius:12px;

    text-decoration:none;
    color:#334155;

    transition:.3s;
}

.menu-link:hover{
    background:#eef2ff;
    color:var(--primary);
}



/* =======================
   MOBILE
======================= */

@media(max-width:768px){

.hero-banner{
    padding:25px;
}

.hero-banner h2{
    font-size:24px;
}

}

</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">

        <button class="btn btn-light me-2"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarMenu">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <div class="brand-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <span class="brand-text">
                Sistem Konsultasi
            </span>
        </a>
     <div class="ms-auto d-flex align-items-center gap-3">

            <div class="clock-box d-none d-md-block" id="clock"></div>

            <div class="text-end d-none d-md-block">

                <span class="d-none d-md-block">
                Halo,
                <strong><?= $nama_user ?></strong>
            </span>

            </div>


            <a href="logout.php"
               class="btn btn-danger btn-sm btn-logout">
                Logout
            </a>
        </div>
     

        </div>
    </div>
</nav>

<!-- SIDEBAR MOBILE -->
<div class="offcanvas offcanvas-start"
     tabindex="-1"
     id="sidebarMenu">

    <div class="offcanvas-header">
        <h5>Menu</h5>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas">
        </button>
    </div>

    <div class="offcanvas-body">

        <div class="text-center mb-4">
            <h5><?= $nama_user ?></h5>
            <small><?= ucfirst($role) ?></small>
        </div>

        <a href="dashboard.php" class="menu-link">
            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>

        <?php if($role=='admin'): ?>

        <a href="dosen.php" class="menu-link">
            <i class="bi bi-people-fill"></i>
            Data Dosen
        </a>
        
        <a href="mahasiswa.php" class="menu-link">
            <i class="bi bi-mortarboard-fill"></i>
            Data Mahasiswa
        </a>

        <a href="jadwal_admin.php" class="menu-link">
            <i class="bi bi-calendar-check-fill"></i>
            Semua Jadwal
        </a>

        <?php endif; ?>

        <?php if($role=='dosen'): ?>

        <a href="jadwal_dosen.php" class="menu-link">
            <i class="bi bi-calendar-week-fill"></i>
            Jadwal Saya
        </a>

        <?php endif; ?>

        <?php if($role=='mahasiswa'): ?>

        <a href="pengajuan.php" class="menu-link">
            <i class="bi bi-file-earmark-plus-fill"></i>
            Buat Pengajuan
        </a>

        <a href="jadwal_saya.php" class="menu-link">
            <i class="bi bi-clock-history"></i>
            Riwayat Saya
        </a>

        <?php endif; ?>

    </div>
</div>

<!-- CONTENT -->
<div class="container py-4">

    <div class="d-flex justify-content-end mb-3">
        <div class="clock-badge" id="clock"></div>
    </div>

    <div class="hero-banner mb-4">

        <h2>
            Selamat Datang,
            <?= $nama_user; ?> 👋
        </h2>

        <p class="mb-0">
            Anda login sebagai
            <strong><?= ucfirst($role); ?></strong>.
            Kelola konsultasi akademik dengan mudah dan cepat.
        </p>

    </div>
    <?php if($role == 'admin'): ?>

<div class="row g-4 mb-4">

    <div class="col-lg-3 col-md-6">

        <div class="card stats-card">

            <div class="card-body d-flex align-items-center">

                <div class="stats-icon bg-purple me-3">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div>

                    <small class="text-muted">
                        Total Dosen
                    </small>

                    <h3 class="mb-0">
                        <?= $total_dosen ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card stats-card">

            <div class="card-body d-flex align-items-center">

                <div class="stats-icon bg-green me-3">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>

                <div>

                    <small class="text-muted">
                        Mahasiswa
                    </small>

                    <h3 class="mb-0">
                        <?= $total_mahasiswa ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card stats-card">

            <div class="card-body d-flex align-items-center">

                <div class="stats-icon bg-blue me-3">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>

                <div>

                    <small class="text-muted">
                        Total Jadwal
                    </small>

                    <h3 class="mb-0">
                        <?= $total_jadwal ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card stats-card">

            <div class="card-body d-flex align-items-center">

                <div class="stats-icon bg-orange me-3">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <div>

                    <small class="text-muted">
                        Menunggu
                    </small>

                    <h3 class="mb-0">
                        <?= $total_menunggu ?>
                    </h3>

                </div>

            </div>

        </div>

    </div>

</div>

<?php endif; ?>
    <div class="row g-4">

        <?php if ($role == 'admin'): ?>

        <div class="col-lg-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">

                    <div class="card-icon icon-primary mb-3">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <h4>Data Dosen</h4>

                    <p class="text-muted">
                        Kelola seluruh data dosen.
                    </p>

                    <a href="dosen.php"
                       class="btn btn-primary w-100">
                        Kelola Dosen
                    </a>

                </div>
            </div>
        </div>
        <div class="col-lg-4">
    <div class="card dashboard-card h-100">
        <div class="card-body text-center p-4">

            <div class="card-icon icon-success mb-3">
                <i class="bi bi-mortarboard-fill"></i>
            </div>

            <h4>Data Mahasiswa</h4>

            <p class="text-muted">
                Kelola data mahasiswa.
            </p>

            <a href="mahasiswa.php"
               class="btn btn-success w-100">
                Kelola Mahasiswa
            </a>

        </div>
    </div>
</div>
        <div class="col-lg-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">

                    <div class="card-icon icon-dark mb-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <h4>Semua Jadwal</h4>

                    <p class="text-muted">
                        Pantau seluruh pengajuan.
                    </p>

                    <a href="jadwal_admin.php"
                       class="btn btn-dark w-100">
                        Lihat Jadwal
                    </a>

                </div>
            </div>
        </div>

        <?php elseif ($role == 'dosen'): ?>

        <div class="col-lg-6 mx-auto">
            <div class="card dashboard-card">
                <div class="card-body text-center p-4">

                    <div class="card-icon icon-success mb-3">
                        <i class="bi bi-calendar-week-fill"></i>
                    </div>

                    <h4>Jadwal Bimbingan Saya</h4>

                    <p class="text-muted">
                        Kelola jadwal mahasiswa.
                    </p>

                    <a href="jadwal_dosen.php"
                       class="btn btn-success w-100">
                        Kelola Jadwal
                    </a>

                </div>
            </div>
        </div>

        <?php elseif ($role == 'mahasiswa'): ?>

        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">

                    <div class="card-icon icon-primary mb-3">
                        <i class="bi bi-file-earmark-plus-fill"></i>
                    </div>

                    <h4>Buat Pengajuan</h4>

                    <a href="pengajuan.php"
                       class="btn btn-primary w-100">
                        Buat Pengajuan
                    </a>

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">

                    <div class="card-icon icon-info mb-3">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <h4>Riwayat Saya</h4>

                    <a href="jadwal_saya.php"
                       class="btn btn-info text-white w-100">
                        Lihat Jadwal
                    </a>

                </div>
            </div>
        </div>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


document.querySelectorAll('.btn-logout').forEach(button => {

    button.addEventListener('click', function(e){

        e.preventDefault();

        const url = this.getAttribute('href');

        Swal.fire({
            title:'Keluar dari sistem?',
            text:'Sesi login akan diakhiri.',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Logout',
            cancelButtonText:'Batal'
        }).then((result)=>{

            if(result.isConfirmed){
                window.location.href = url;
            }

        });

    });

});
</script>

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
<?php if(isset($_SESSION['login_success'])): ?>

<script>
document.addEventListener('DOMContentLoaded', function(){

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Login berhasil',
        text: 'Selamat datang <?= htmlspecialchars($_SESSION['nama_mhs']) ?>',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

});
</script>

<?php
unset($_SESSION['login_success']);
endif;
?>
</body>
</html>
