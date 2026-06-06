<?php
session_start();
require_once 'config/koneksi.php';

if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $npm = mysqli_real_escape_string($koneksi, trim($_POST['npm']));
    $password = trim($_POST['password']);

    if (empty($npm) || empty($password)) {
        $error = "Username dan Password tidak boleh kosong!";
    } else {
        $password_hash = md5($password);

        // 1. Cek di tabel users (Untuk Admin & Mahasiswa)
        $query_users = "SELECT * FROM users WHERE npm = '$npm' AND password = '$password_hash'";
        $result_users = mysqli_query($koneksi, $query_users);

        if (mysqli_num_rows($result_users) > 0) {
            $data = mysqli_fetch_assoc($result_users);
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['npm']     = $data['npm'];
            $_SESSION['nama_mhs']= $data['nama_mhs'];
            $_SESSION['role']    = $data['role']; // admin / mahasiswa

            $_SESSION['login_success'] = true;
            header("Location: dashboard.php");
            exit;

        } else {
            // 2. Cek di tabel dosen (Untuk Dosen)
            $query_dosen = "SELECT * FROM dosen WHERE nip = '$npm' AND password = '$password_hash'";
            $result_dosen = mysqli_query($koneksi, $query_dosen);
            
            if (mysqli_num_rows($result_dosen) > 0) {
                $data = mysqli_fetch_assoc($result_dosen);
                // Simpan data dosen ke session
                $_SESSION['id_user']  = $data['id_dosen'];  // id_dosen dijadikan id_user di session
                $_SESSION['npm']      = $data['nip'];       
                $_SESSION['nama_mhs'] = $data['nama_dosen'];
                $_SESSION['role']     = 'dosen';            // Set role jadi dosen
                
                $_SESSION['login_success'] = true;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Username atau Password salah!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Jadwal Konsultasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
       :root{
    --primary:#4f46e5;
    --secondary:#7c3aed;
    --dark:#0f172a;
    --light:#f8fafc;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    min-height:100vh;

    background:
    linear-gradient(
        135deg,
        #eef2ff,
        #f8fafc,
        #dbeafe
    );

    font-family:'Segoe UI',sans-serif;

    display:flex;
    align-items:center;
    justify-content:center;

    overflow-x:hidden;
}

.login-wrapper{
    width:100%;
    max-width:1200px;
    margin:30px;
}

.login-card{
    background:white;

    border:none;

    border-radius:32px;

    overflow:hidden;

    box-shadow:
    0 25px 60px rgba(0,0,0,.08);
}

/* LEFT SIDE */

.left-panel{

    background:
    linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color:white;

    padding:60px;

    height:100%;

    position:relative;
}

.left-panel::before{
    content:"";

    position:absolute;

    width:250px;
    height:250px;

    border-radius:50%;

    background:rgba(255,255,255,.08);

    top:-80px;
    right:-80px;
}

.left-panel::after{
    content:"";

    position:absolute;

    width:180px;
    height:180px;

    border-radius:50%;

    background:rgba(255,255,255,.08);

    bottom:-50px;
    left:-50px;
}

.logo-box{
    width:90px;
    height:90px;

    border-radius:25px;

    background:rgba(255,255,255,.15);

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:40px;

    margin-bottom:25px;
}

.left-panel h2{
    font-weight:700;
}

.left-panel p{
    opacity:.9;
}

.feature-box{
    background:rgba(255,255,255,.12);

    border:1px solid rgba(255,255,255,.15);

    border-radius:18px;

    padding:15px;

    margin-top:15px;
}

/* RIGHT SIDE */

.right-panel{
    padding:50px;
}

.login-title{
    font-weight:700;
    color:#0f172a;
}

.login-subtitle{
    color:#64748b;
}

.form-control{
    border-radius:14px;
    height:55px;
    border:1px solid #dbe2ea;
}

.form-control:focus{
    border-color:#6366f1;

    box-shadow:
    0 0 0 4px rgba(99,102,241,.15);
}

.btn-login{

    height:55px;

    border:none;

    border-radius:14px;

    background:
    linear-gradient(
    135deg,
    #4f46e5,
    #7c3aed);

    font-weight:600;

    transition:.3s;
}

.btn-login:hover{
    transform:translateY(-2px);
}

.info-box{

    background:#eef2ff;

    border-radius:14px;

    padding:15px;

    color:#4338ca;

    font-size:14px;
}

.footer-text{
    color:#94a3b8;
    font-size:13px;
}

/* MOBILE */

@media(max-width:991px){

.left-panel{
    display:none;
}

.right-panel{
    padding:35px;
}

.login-card{
    border-radius:25px;
}

.swal2-popup{
    border-radius:24px !important;
}

.swal2-title{
    font-weight:700 !important;
}

.swal2-confirm{
    border-radius:12px !important;
    padding:10px 24px !important;
    font-weight:600 !important;
}

}
    </style>
</head>
<body>
<div class="login-wrapper">

    <div class="card login-card">

        <div class="row g-0">

            <!-- LEFT -->
            <div class="col-lg-6">

                <div class="left-panel">

                    <div class="logo-box">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>

                    <h2>
                        Sistem Konsultasi Akademik
                    </h2>

                    <p class="mt-3">
                        Platform pengelolaan bimbingan mahasiswa dan dosen
                        yang cepat, modern, dan terintegrasi.
                    </p>

                    <div class="feature-box">
                        <h6>
                            <i class="bi bi-calendar-check me-2"></i>
                            Pengajuan Online
                        </h6>
                        <small>
                            Ajukan jadwal konsultasi kapan saja.
                        </small>
                    </div>

                    <div class="feature-box">
                        <h6>
                            <i class="bi bi-clock-history me-2"></i>
                            Riwayat Lengkap
                        </h6>
                        <small>
                            Pantau seluruh aktivitas bimbingan.
                        </small>
                    </div>

                    <div class="feature-box">
                        <h6>
                            <i class="bi bi-shield-lock me-2"></i>
                            Aman & Terintegrasi
                        </h6>
                        <small>
                            Data tersimpan secara terpusat.
                        </small>
                    </div>

                </div>

            </div>

            <!-- RIGHT -->
            <div class="col-lg-6">

                <div class="right-panel">

                    <h2 class="login-title">
                        Selamat Datang 👋
                    </h2>

                    <p class="login-subtitle mb-4">
                        Silakan login untuk melanjutkan
                    </p>

                    <div class="info-box mb-4">

                        <b>Mahasiswa</b> menggunakan NPM

                        <br>

                        <b>Dosen</b> menggunakan NIP

                    </div>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                NPM / NIP
                            </label>

                           <input
                            type="text"
                            id="npm"
                            name="npm"
                            class="form-control"
                            placeholder="Masukkan NPM atau NIP"
                            required>

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                required>

                        </div>

                        <button
                            type="submit"
                            name="login"
                            class="btn btn-primary btn-login w-100">

                            <i class="bi bi-box-arrow-in-right me-2"></i>

                            Masuk ke Sistem

                        </button>

                    </form>

                    <div class="text-center mt-4 footer-text">

                        © <?= date('Y') ?>
                        Sistem Konsultasi Akademik

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($error != ""): ?>
<script>

document.addEventListener('DOMContentLoaded', function(){

    Swal.fire({

        icon: 'error',

        title: 'Login Gagal',

        html: `
            <div style="font-size:15px; line-height:1.8;">
                <b>NPM/NIP atau password tidak sesuai.</b>
                <br><br>
                Pastikan:
                <br>
                • Mahasiswa menggunakan NPM
                <br>
                • Dosen menggunakan NIP
                <br>
                • Password sesuai dengan akun Anda
            </div>
        `,

        confirmButtonText: `
            <i class="bi bi-arrow-repeat"></i>
            Coba Lagi
        `,

        confirmButtonColor: '#4f46e5',

        background: '#ffffff',

        allowOutsideClick: false,

        allowEscapeKey: true,

        customClass: {
            popup: 'rounded-4 shadow-lg'
        },

        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },

        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }

    }).then(() => {

        document.getElementById('npm').focus();

        document.getElementById('npm').select();

    });

});

</script>
<?php endif; ?>
<script>

document.querySelector('form').addEventListener('submit', function(){

    Swal.fire({

        title: 'Memverifikasi Akun',

        html: 'Mohon tunggu sebentar...',

        allowOutsideClick: false,

        allowEscapeKey: false,

        showConfirmButton: false,

        didOpen: () => {
            Swal.showLoading();
        }

    });

});

</script>
</body>
</html>
