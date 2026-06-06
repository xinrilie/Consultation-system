<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id_user'];

$data = mysqli_fetch_assoc(
    mysqli_query(
        $koneksi,
        "SELECT *
         FROM users
         WHERE id_user='$id'"
    )
);

if(isset($_POST['simpan']))
{
    $nama = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama_mhs']
    );

    $prodi = mysqli_real_escape_string(
        $koneksi,
        $_POST['prodi']
    );

    $fakultas = mysqli_real_escape_string(
        $koneksi,
        $_POST['fakultas']
    );

    mysqli_query(
        $koneksi,
        "UPDATE users SET
        nama_mhs='$nama',
        prodi='$prodi',
        fakultas='$fakultas'
        WHERE id_user='$id'"
    );

    $_SESSION['profil_updated'] = true;

    header("Location: profil_mahasiswa.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<title>Profil Mahasiswa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f1f5f9;
}

.profile-card{

    border:none;

    border-radius:24px;

    box-shadow:
    0 10px 25px rgba(0,0,0,.06);

}

.profile-header{

    background:
    linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color:white;

    padding:30px;

}

.profile-avatar{

    width:100px;
    height:100px;

    border-radius:50%;

    background:white;

    color:#4f46e5;

    font-size:40px;

    display:flex;

    align-items:center;
    justify-content:center;

    margin:auto;

}

.form-control{

    border-radius:12px;
    height:50px;

}

.btn-save{

    background:
    linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    border:none;

    border-radius:12px;

}

</style>

</head>

<body>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-lg-7">

<div class="card profile-card">

<div class="profile-header text-center">

    <div class="profile-avatar mb-3">

        <i class="bi bi-person-fill"></i>

    </div>

    <h3>
        <?= $data['nama_mhs']; ?>
    </h3>

    <p class="mb-0">
        <?= $data['prodi']; ?>
    </p>

</div>

<div class="card-body p-4">

<form method="POST">

<div class="mb-3">

<label class="form-label fw-semibold">

Nama Mahasiswa

</label>

<input
type="text"
name="nama_mhs"
class="form-control"
value="<?= $data['nama_mhs']; ?>"
required>

</div>

<div class="mb-3">

<label class="form-label fw-semibold"> 
    NPM
</label>
<input
type="text"
class="form-control"
value="<?= $data['npm']; ?>"
readonly>

<label class="form-label fw-semibold">

Program Studi

</label>

<input
type="text"
name="prodi"
class="form-control"
value="<?= $data['prodi']; ?>"
required>

</div>

<div class="mb-4">

<label class="form-label fw-semibold">

Fakultas

</label>

<input
type="text"
name="fakultas"
class="form-control"
value="<?= $data['fakultas']; ?>"
required>

</div>

<div class="d-flex gap-2">

<button
type="submit"
name="simpan"
class="btn btn-save text-white">

<i class="bi bi-check-circle"></i>
Simpan Perubahan

</button>

<a href="dashboard.php"
class="btn btn-secondary">

Kembali

</a>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['profil_updated'])): ?>

<script>

Swal.fire({

    toast:true,

    position:'top-end',

    icon:'success',

    title:'Profil berhasil diperbarui',

    showConfirmButton:false,

    timer:3000,

    timerProgressBar:true

});

</script>

<?php
unset($_SESSION['profil_updated']);
endif;
?>

</body>
</html>
