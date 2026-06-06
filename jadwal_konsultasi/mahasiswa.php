<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan_sukses = "";
$pesan_error = "";

/* ==========================
   TAMBAH MAHASISWA
========================== */

if(isset($_POST['tambah'])){

    $npm = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['npm'])
    );

    $nama_mhs = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama_mhs'])
    );
$prodi = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['prodi'])
);

$fakultas = mysqli_real_escape_string(
    $koneksi,
    trim($_POST['fakultas'])
);

    if(
    empty($npm) ||
    empty($nama_mhs) ||
    empty($prodi) ||
    empty($fakultas)
){

        $pesan_error = "Semua field wajib diisi.";

    }else{

        $cek = mysqli_query(
            $koneksi,
            "SELECT * FROM users
             WHERE npm='$npm'"
        );

        if(mysqli_num_rows($cek) > 0){

            $pesan_error = "NPM sudah terdaftar.";

        }else{

            $password = md5('12345');

            $insert = mysqli_query(
                $koneksi,
                "INSERT INTO users
(
    npm,
    password,
    nama_mhs,
    prodi,
    fakultas,
    role
)
                VALUES
(
    '$npm',
    '$password',
    '$nama_mhs',
    '$prodi',
    '$fakultas',
    'mahasiswa'
)"
            );

            if($insert){

                $pesan_sukses =
                "Mahasiswa berhasil ditambahkan.";

            }else{

                $pesan_error =
                "Gagal menambahkan mahasiswa.";
            }
        }
    }
}

/* ==========================
   UPDATE MAHASISWA
========================== */

if(isset($_POST['update'])){

    $id_user = intval($_POST['id_user']);

    $npm = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['npm'])
    );

    $nama_mhs = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama_mhs'])
    );

    $password_baru = trim(
        $_POST['password']
    );

    $cek = mysqli_query(
        $koneksi,
        "SELECT * FROM users
        WHERE npm='$npm'
        AND id_user!='$id_user'"
    );

    if(mysqli_num_rows($cek) > 0){

        $pesan_error = "NPM sudah digunakan.";

    }else{

        mysqli_query(
            $koneksi,
            "UPDATE users SET

            npm='$npm',
            nama_mhs='$nama_mhs'

            WHERE id_user='$id_user'"
        );

        if(!empty($password_baru)){

            $password_md5 = md5($password_baru);

            mysqli_query(
                $koneksi,
                "UPDATE users SET

npm='$npm',
nama_mhs='$nama_mhs',
prodi='$prodi',
fakultas='$fakultas'"
            );
        }

        $pesan_sukses =
        "Data mahasiswa berhasil diperbarui.";
    }
}

/* ==========================
   HAPUS MAHASISWA
========================== */

if(isset($_GET['hapus'])){

    $id = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM jadwal
        WHERE id_user='$id'"
    );

    mysqli_query(
        $koneksi,
        "DELETE FROM users
        WHERE id_user='$id'
        AND role='mahasiswa'"
    );

    $_SESSION['hapus_sukses'] = true;

    header("Location: mahasiswa.php");
    exit;
}

/* ==========================
   SEARCH
========================== */

$cari = "";

if(isset($_GET['cari'])){

    $cari = mysqli_real_escape_string(
        $koneksi,
        trim($_GET['cari'])
    );
}

/* ==========================
   TOTAL MAHASISWA
========================== */

$total_mahasiswa =
mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT *
         FROM users
         WHERE role='mahasiswa'"
    )
);

/* ==========================
   DATA MAHASISWA
========================== */

$query_mahasiswa =
mysqli_query(
    $koneksi,

    "SELECT *
    FROM users

    WHERE role='mahasiswa'

    AND (

        npm LIKE '%$cari%'
        OR
        nama_mhs LIKE '%$cari%'

    )

    ORDER BY id_user DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Kelola Mahasiswa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<style>

:root{
    --primary:#4f46e5;
    --secondary:#7c3aed;
    --success:#10b981;
    --dark:#0f172a;
    --light:#f8fafc;
}

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

/* NAVBAR */

.navbar-custom{
    background:rgba(255,255,255,.95);
    backdrop-filter:blur(10px);
    box-shadow:0 3px 20px rgba(0,0,0,.05);
}

/* HERO */

.hero-banner{
    background:linear-gradient(
        135deg,
        var(--primary),
        var(--secondary)
    );

    color:white;

    border-radius:28px;

    padding:35px;

    position:relative;

    overflow:hidden;

    box-shadow:
    0 15px 40px rgba(79,70,229,.20);
}

.hero-banner::before{

    content:"";

    position:absolute;

    width:220px;
    height:220px;

    border-radius:50%;

    background:rgba(255,255,255,.08);

    right:-70px;
    top:-70px;
}

.hero-banner::after{

    content:"";

    position:absolute;

    width:180px;
    height:180px;

    border-radius:50%;

    background:rgba(255,255,255,.08);

    bottom:-60px;
    right:50px;
}

.hero-banner h2{
    font-weight:700;
}

/* CARD */

.card-modern{

    border:none;

    border-radius:25px;

    overflow:hidden;

    background:white;

    box-shadow:
    0 10px 25px rgba(0,0,0,.06);
}

/* STAT CARD */

.stat-card{

    background:
    linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color:white;

    border-radius:25px;

    padding:25px;

    display:flex;

    justify-content:space-between;

    align-items:center;
}

.stat-card i{

    font-size:55px;

    opacity:.7;
}

.stat-card h2{
    font-weight:700;
}

/* FORM */

.form-control{

    border-radius:14px;

    min-height:50px;

    border:1px solid #dbe2ea;
}

.form-control:focus{

    border-color:#6366f1;

    box-shadow:
    0 0 0 4px rgba(99,102,241,.15);
}

.btn-modern{

    border:none;

    border-radius:14px;

    min-height:50px;

    font-weight:600;
}

/* TABLE */

.table-modern thead{

    background:#eef2ff;
}

.table-modern tbody tr{

    transition:.25s;
}

.table-modern tbody tr:hover{

    background:#f8fafc;

    transform:scale(1.01);
}

/* BADGE */

.badge-role{

    background:
    linear-gradient(
        135deg,
        #4f46e5,
        #7c3aed
    );

    color:white;

    padding:8px 12px;

    border-radius:20px;
}

/* SEARCH */

.search-box{

    position:relative;
}

.search-box i{

    position:absolute;

    top:15px;
    left:15px;

    color:#64748b;
}

.search-box input{

    padding-left:42px;
}

/* MOBILE */

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
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">

<div class="container">

<a href="dashboard.php"
class="btn btn-light me-3">

<i class="bi bi-arrow-left"></i>

</a>

<a class="navbar-brand fw-bold">

<i class="bi bi-mortarboard-fill me-2"></i>

Kelola Mahasiswa

</a>

<div class="ms-auto">

<a href="dashboard.php"
class="btn btn-outline-primary">

Dashboard

</a>

</div>

</div>

</nav>

<div class="container py-4">
    <div class="hero-banner mb-4">

<h2>
Data Mahasiswa
</h2>

<p class="mb-0">

Kelola seluruh akun mahasiswa
yang terdaftar pada sistem konsultasi.

</p>

</div>

<div class="stat-card mb-4">

<div>

<small>Total Mahasiswa</small>

<h2>

<?= $total_mahasiswa ?>

</h2>

</div>

<i class="bi bi-mortarboard-fill"></i>

</div>
<div class="row g-4">
    <div class="col-lg-4">

<div class="card card-modern">

<div class="card-header bg-primary text-white">

Tambah Mahasiswa

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label class="form-label">

NPM

</label>

<input
type="text"
name="npm"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Nama Mahasiswa

</label>

<input
type="text"
name="nama_mhs"
class="form-control"
required>

</div>
<div class="mb-3">
    <label class="form-label">Program Studi</label>
    <input
        type="text"
        name="prodi"
        class="form-control"
        required>
</div>

<div class="mb-3">
    <label class="form-label">Fakultas</label>
    <input
        type="text"
        name="fakultas"
        class="form-control"
        required>
</div>
<div class="alert alert-info">

Password default:
<strong>12345</strong>

</div>

<button
type="submit"
name="tambah"
class="btn btn-primary btn-modern w-100">

<i class="bi bi-plus-circle me-2"></i>

Tambah Mahasiswa

</button>

</form>

</div>

</div>

</div>
<div class="col-lg-8">

<div class="card card-modern">

<div class="card-body"><form method="GET" class="mb-4">

<div class="search-box">

<i class="bi bi-search"></i>

<input
type="text"
name="cari"
value="<?= $cari ?>"
class="form-control"
placeholder="Cari NPM atau Nama Mahasiswa">

</div>

</form>
<div class="table-responsive">

<table class="table table-hover align-middle table-modern">

    <thead>

        <tr>

            <th width="70">No</th>
<th>NPM</th>
<th>Nama Mahasiswa</th>
<th>Program Studi</th>
<th>Fakultas</th>
<th width="180">Aksi</th>

        </tr>

    </thead>

    <tbody>

    <?php

    $no = 1;

    while($row = mysqli_fetch_assoc($query_mahasiswa)):

    ?>

        <tr>

            <td><?= $no++ ?></td>

            <td>

                <strong>

                    <?= htmlspecialchars($row['npm']) ?>

                </strong>

            </td>
<td>

<?= htmlspecialchars($row['nama_mhs']) ?>

</td>

<td>

<?= htmlspecialchars($row['prodi'] ?? '-') ?>

</td>

<td>

<?= htmlspecialchars($row['fakultas'] ?? '-') ?>

</td>

            <td>

                <button
                    class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#edit<?= $row['id_user'] ?>">

                    <i class="bi bi-pencil-square"></i>

                </button>

                <a href="mahasiswa.php?hapus=<?= $row['id_user'] ?>"
                   class="btn btn-danger btn-sm btn-hapus">

                    <i class="bi bi-trash"></i>

                </a>

            </td>

        </tr>
        <div
class="modal fade"
id="edit<?= $row['id_user'] ?>"
tabindex="-1">

<div class="modal-dialog">

<div class="modal-content border-0 rounded-4">

<div class="modal-header bg-warning">

<h5 class="modal-title">

Edit Mahasiswa

</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<form method="POST">

<input
type="hidden"
name="id_user"
value="<?= $row['id_user'] ?>">

<div class="modal-body">

<div class="mb-3">

<label>NPM</label>

<input
type="text"
name="npm"
class="form-control"
value="<?= htmlspecialchars($row['npm']) ?>"
required>

</div>

<div class="mb-3">

<label>Nama Mahasiswa</label>

<input
type="text"
name="nama_mhs"
class="form-control"
value="<?= htmlspecialchars($row['nama_mhs']) ?>"
required>

</div>
<div class="mb-3">

<label>Program Studi</label>

<input
type="text"
name="prodi"
class="form-control"
value="<?= htmlspecialchars($row['prodi'] ?? '') ?>"
required>

</div>

<div class="mb-3">

<label>Fakultas</label>

<input
type="text"
name="fakultas"
class="form-control"
value="<?= htmlspecialchars($row['fakultas'] ?? '') ?>"
required>

</div>
<div class="mb-3">

<label>Password Baru</label>

<input
type="password"
name="password"
class="form-control">

<small class="text-muted">

Kosongkan jika tidak ingin mengganti password.

</small>

</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Batal

</button>

<button
type="submit"
name="update"
class="btn btn-warning">

Simpan Perubahan

</button>

</div>

</form>

</div>

</div>

</div>
<?php endwhile; ?>
<?php if(mysqli_num_rows($query_mahasiswa)==0): ?>

<tr>

<td colspan="6" class="text-center py-4">

Tidak ada data mahasiswa.

</td>

</tr>

<?php endif; ?>
    </tbody>

</table>

</div>
</div>

</div>

</div>
</div>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if($pesan_sukses!=""): ?>

<script>

Swal.fire({

    icon:'success',

    title:'Berhasil',

    text:'<?= $pesan_sukses ?>',

    timer:2500,

    showConfirmButton:false

});

</script>

<?php endif; ?>

<?php if($pesan_error!=""): ?>

<script>

Swal.fire({

    icon:'error',

    title:'Oops...',

    text:'<?= $pesan_error ?>'

});

</script>

<?php endif; ?>

<?php

if(isset($_SESSION['hapus_sukses'])):

unset($_SESSION['hapus_sukses']);

?>

<script>

Swal.fire({

    icon:'success',

    title:'Terhapus',

    text:'Mahasiswa berhasil dihapus',

    timer:2000,

    showConfirmButton:false

});

</script>

<?php endif; ?>

<script>

document
.querySelectorAll('.btn-hapus')
.forEach(button=>{

    button.addEventListener(
    'click',

    function(e){

        e.preventDefault();

        const url =
        this.getAttribute('href');

        Swal.fire({

            title:'Hapus mahasiswa?',

            html:
            'Data mahasiswa dan seluruh jadwal konsultasi terkait akan dihapus.',

            icon:'warning',

            showCancelButton:true,

            confirmButtonColor:'#dc3545',

            cancelButtonText:'Batal',

            confirmButtonText:'Ya, Hapus'

        }).then((result)=>{

            if(result.isConfirmed){

                window.location.href = url;

            }

        });

    });

});

</script>

</body>
</html>
