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
            $_SESSION['role']    = $data['role']; 
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
    <style>
        body { background-color: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-container { width: 100%; padding: 15px; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="row justify-content-center m-0">
        <div class="col-12 col-sm-8 col-md-5 col-lg-4">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="mb-0">Sistem Konsultasi Akademik</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info text-center" style="font-size: 13px;">
                        <b>Mahasiswa:</b> Gunakan NPM | <b>Dosen:</b> Gunakan NIP
                    </div>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="npm" class="form-label fw-bold">Masukkan NPM / NIP</label>
                            <input type="text" class="form-control" id="npm" name="npm" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold">Masuk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($error != ""): ?>
<script>Swal.fire({ icon: 'error', title: 'Gagal Login!', text: '<?= $error; ?>' });</script>
<?php endif; ?>

</body>
</html>