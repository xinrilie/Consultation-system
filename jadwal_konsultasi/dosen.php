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
   TAMBAH DOSEN
========================== */

if(isset($_POST['tambah'])){

    $nip = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nip'])
    );

    $nama_dosen = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama_dosen'])
    );

    $kategori = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['kategori_keahlian'])
    );

    if(
        empty($nip) ||
        empty($nama_dosen) ||
        empty($kategori)
    ){

        $pesan_error =
        "Semua field wajib diisi.";

    }else{

        $cek = mysqli_query(
            $koneksi,
            "SELECT *
             FROM dosen
             WHERE nip='$nip'"
        );

        if(mysqli_num_rows($cek)>0){

            $pesan_error =
            "NIP sudah terdaftar.";

        }else{

            $password = md5('12345');

            $insert = mysqli_query(
                $koneksi,
                "INSERT INTO dosen
                (
                    nip,
                    nama_dosen,
                    kategori_keahlian,
                    password
                )
                VALUES
                (
                    '$nip',
                    '$nama_dosen',
                    '$kategori',
                    '$password'
                )"
            );

            if($insert){

                $pesan_sukses =
                "Dosen berhasil ditambahkan.";

            }else{

                $pesan_error =
                "Gagal menambahkan dosen.";

            }
        }
    }
}

/* ==========================
   UPDATE DOSEN
========================== */

if(isset($_POST['update'])){

    $id_dosen = intval($_POST['id_dosen']);

    $nip = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nip'])
    );

    $nama_dosen = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama_dosen'])
    );

    $kategori = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['kategori_keahlian'])
    );

    $password_baru =
    trim($_POST['password']);

    $cek = mysqli_query(
        $koneksi,
        "SELECT *
        FROM dosen

        WHERE nip='$nip'

        AND id_dosen!='$id_dosen'"
    );

    if(mysqli_num_rows($cek)>0){

        $pesan_error =
        "NIP sudah digunakan.";

    }else{

        mysqli_query(
            $koneksi,
            "UPDATE dosen SET

            nip='$nip',
            nama_dosen='$nama_dosen',
            kategori_keahlian='$kategori'

            WHERE id_dosen='$id_dosen'"
        );

        if(!empty($password_baru)){

            $password_md5 =
            md5($password_baru);

            mysqli_query(
                $koneksi,
                "UPDATE dosen SET

                password='$password_md5'

                WHERE id_dosen='$id_dosen'"
            );
        }

        $pesan_sukses =
        "Data dosen berhasil diperbarui.";
    }
}

/* ==========================
   HAPUS DOSEN
========================== */

if(isset($_GET['hapus'])){

    $id = intval($_GET['hapus']);

    mysqli_query(
        $koneksi,
        "DELETE FROM jadwal
         WHERE id_dosen='$id'"
    );

    mysqli_query(
        $koneksi,
        "DELETE FROM dosen
         WHERE id_dosen='$id'"
    );

    $_SESSION['hapus_sukses']=true;

    header("Location:dosen.php");
    exit;
}

/* ==========================
   SEARCH
========================== */

$cari="";

if(isset($_GET['cari'])){

    $cari = mysqli_real_escape_string(
        $koneksi,
        trim($_GET['cari'])
    );
}

/* ==========================
   TOTAL DOSEN
========================== */

$total_dosen =
mysqli_num_rows(
    mysqli_query(
        $koneksi,
        "SELECT * FROM dosen"
    )
);

/* ==========================
   DATA DOSEN
========================== */

$query_dosen =
mysqli_query(
    $koneksi,

    "SELECT *

    FROM dosen

    WHERE

    nip LIKE '%$cari%'

    OR

    nama_dosen LIKE '%$cari%'

    OR

    kategori_keahlian LIKE '%$cari%'

    ORDER BY id_dosen DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Kelola Data Dosen</title>

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

/* ======================
   NAVBAR
====================== */

.navbar-custom{
    background:white;
    box-shadow:0 4px 20px rgba(0,0,0,.05);
}

.brand-logo{
    width:42px;
    height:42px;

    border-radius:12px;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;

    background:
    linear-gradient(
        135deg,
        var(--success),
        #34d399
    );
}

/* ======================
   HERO
====================== */

.hero-banner{

    background:
    linear-gradient(
        135deg,
        var(--success),
        #34d399
    );

    color:white;

    padding:35px;

    border-radius:28px;

    overflow:hidden;

    position:relative;

    box-shadow:
    0 15px 40px rgba(16,185,129,.20);
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

.hero-banner h2{
    font-weight:700;
}

/* ======================
   STAT CARD
====================== */

.stats-card{

    border:none;

    border-radius:24px;

    background:white;

    box-shadow:
    0 10px 25px rgba(0,0,0,.05);
}

.stats-icon{

    width:70px;
    height:70px;

    border-radius:20px;

    background:
    linear-gradient(
        135deg,
        var(--success),
        #34d399
    );

    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:28px;
}

/* ======================
   MAIN CARD
====================== */

.main-card{

    border:none;

    border-radius:25px;

    overflow:hidden;

    box-shadow:
    0 10px 30px rgba(0,0,0,.05);
}

.card-header-modern{

    background:white;

    border:none;

    padding:20px 25px;

    font-weight:700;
}

/* ======================
   FORM
====================== */

.form-control{

    border-radius:14px;

    min-height:52px;

    border:1px solid #dbe2ea;
}

.form-control:focus{

    border-color:#10b981;

    box-shadow:
    0 0 0 4px rgba(16,185,129,.12);
}

.btn-save{

    border:none;

    height:52px;

    border-radius:14px;

    font-weight:600;

    background:
    linear-gradient(
        135deg,
        var(--success),
        #34d399
    );
}

/* ======================
   TABLE
====================== */

.table th{

    border:none;

    background:#f8fafc;

    color:#64748b;
}

.table td{
    vertical-align:middle;
}

.table th,
.table td{
    padding: 15px 18px;
    vertical-align: middle;
}


/* ======================
   BADGE
====================== */

.badge-keahlian{

    background:
    linear-gradient(
        135deg,
        #10b981,
        #34d399
    );

    color:white;

    padding:8px 14px;

    border-radius:50px;
}

/* ======================
   SEARCH
====================== */

.search-box{

    position:relative;
}

.search-box i{

    position:absolute;

    left:15px;
    top:16px;

    color:#94a3b8;
}

.search-box input{
    padding-left:45px;
}

/* ======================
   BUTTON
====================== */

.btn-action{

    border-radius:12px;
}

</style>
</head>    
<body>

<nav class="navbar navbar-expand-lg navbar-custom mb-4">

    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2"
           href="dashboard.php">

            <div class="brand-logo">
                <i class="bi bi-person-workspace"></i>
            </div>

            <strong>
                Kelola Dosen
            </strong>

        </a>

        <a href="dashboard.php"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left"></i>
            Dashboard

        </a>

    </div>

</nav>

<div class="container">

    <!-- HERO -->

    <div class="hero-banner mb-4">

        <h2>
            Data Dosen
        </h2>

        <p class="mb-0">

            Kelola seluruh data dosen,
            keahlian dan akun dosen
            dalam satu halaman.

        </p>

    </div>

    <!-- STAT -->

    <div class="row mb-4">

        <div class="col-lg-4">

            <div class="card stats-card">

                <div class="card-body d-flex align-items-center">

                    <div class="stats-icon me-3">

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

    </div>

    <div class="row g-4">

        <!-- FORM TAMBAH -->

        <div class="col-lg-4">

            <div class="card main-card">

                <div class="card-header card-header-modern">

                    Tambah Dosen

                </div>

                <div class="card-body">

                    <form method="POST">

                        <div class="mb-3">

                            <label class="fw-semibold">

                                NIP

                            </label>

                            <input
                            type="text"
                            name="nip"
                            class="form-control"
                            required>

                        </div>

                        <div class="mb-3">

                            <label class="fw-semibold">

                                Nama Dosen

                            </label>

                            <input
                            type="text"
                            name="nama_dosen"
                            class="form-control"
                            required>

                        </div>

                        <div class="mb-3">

                            <label class="fw-semibold">

                                Keahlian

                            </label>

                            <input
                            type="text"
                            name="kategori_keahlian"
                            class="form-control"
                            required>

                        </div>

                        <div class="alert alert-success">

                            Password default:
                            <strong>12345</strong>

                        </div>

                        <button
                        type="submit"
                        name="tambah"
                        class="btn btn-save text-white w-100">

                            <i class="bi bi-plus-circle me-2"></i>

                            Tambah Dosen

                        </button>

                    </form>

                </div>

            </div>

        </div>
        <!-- DAFTAR DOSEN -->

<div class="col-lg-8">

    <div class="card main-card">

        <div class="card-header card-header-modern">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <span>
                    Daftar Dosen
                </span>

                <form method="GET" class="search-box">

                    <i class="bi bi-search"></i>

                    <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari NIP, Nama atau Keahlian..."
                    value="<?= htmlspecialchars($cari) ?>">

                </form>

            </div>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th width="80" class="text-center">
                                No
                            </th>

                            <th>
                                NIP
                            </th>

                            <th>
                                Nama Dosen
                            </th>

                            <th>
                                Keahlian
                            </th>

                            <th width="180" class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $no = 1;

                        while($row = mysqli_fetch_assoc($query_dosen)):
                        ?>

                        <tr>

                            <td class="text-center fw-semibold">
                                <?= $no++ ?>
                            </td>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($row['nip']) ?>
                                </strong>

                            </td>

                            <td>

                                <?= htmlspecialchars($row['nama_dosen']) ?>

                            </td>

                            <td>

                                <span class="badge-keahlian">

                                    <?= htmlspecialchars($row['kategori_keahlian']) ?>

                                </span>

                            </td>

                            <td>

                                <button
                                class="btn btn-warning btn-sm btn-action"

                                data-bs-toggle="modal"

                                data-bs-target="#editModal<?= $row['id_dosen'] ?>">

                                    <i class="bi bi-pencil-square"></i>

                                </button>

                                <a
                                href="?hapus=<?= $row['id_dosen'] ?>"

                                class="btn btn-danger btn-sm btn-hapus btn-action">

                                    <i class="bi bi-trash"></i>

                                </a>

                            </td>

                        </tr>

                        <!-- MODAL EDIT -->

                        <div class="modal fade"
                             id="editModal<?= $row['id_dosen'] ?>"
                             tabindex="-1">

                            <div class="modal-dialog">

                                <div class="modal-content border-0 rounded-4">

                                    <form method="POST">

                                        <input
                                        type="hidden"
                                        name="id_dosen"
                                        value="<?= $row['id_dosen'] ?>">

                                        <div class="modal-header">

                                            <h5 class="modal-title">

                                                Edit Dosen

                                            </h5>

                                            <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal">
                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <div class="mb-3">

                                                <label class="fw-semibold">

                                                    NIP

                                                </label>

                                                <input
                                                type="text"
                                                name="nip"
                                                class="form-control"

                                                value="<?= htmlspecialchars($row['nip']) ?>"

                                                required>

                                            </div>

                                            <div class="mb-3">

                                                <label class="fw-semibold">

                                                    Nama Dosen

                                                </label>

                                                <input
                                                type="text"
                                                name="nama_dosen"
                                                class="form-control"

                                                value="<?= htmlspecialchars($row['nama_dosen']) ?>"

                                                required>

                                            </div>

                                            <div class="mb-3">

                                                <label class="fw-semibold">

                                                    Keahlian

                                                </label>

                                                <input
                                                type="text"
                                                name="kategori_keahlian"
                                                class="form-control"

                                                value="<?= htmlspecialchars($row['kategori_keahlian']) ?>"

                                                required>

                                            </div>

                                            <div class="mb-3">

                                                <label class="fw-semibold">

                                                    Password Baru

                                                </label>

                                                <input
                                                type="password"
                                                name="password"
                                                class="form-control">

                                                <small class="text-muted">

                                                    Kosongkan jika tidak ingin mengganti password

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
                                            class="btn btn-success">

                                                Simpan Perubahan

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</div> <!-- row -->

</div> <!-- container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

const searchInput =
document.querySelector('input[name="cari"]');

if(searchInput){

    searchInput.addEventListener('keyup',function(){

        clearTimeout(window.searchTimer);

        window.searchTimer = setTimeout(()=>{

            this.form.submit();

        },500);

    });

}

</script>

<script>

document.querySelectorAll('form').forEach(form=>{

    form.addEventListener('submit',function(){

        Swal.fire({

            title:'Menyimpan Data',

            text:'Mohon tunggu sebentar...',

            allowOutsideClick:false,

            allowEscapeKey:false,

            showConfirmButton:false,

            didOpen:()=>{

                Swal.showLoading();

            }

        });

    });

});

</script>

<script>

document.querySelectorAll('.btn-hapus').forEach(button=>{

    button.addEventListener('click',function(e){

        e.preventDefault();

        const url = this.getAttribute('href');

        Swal.fire({

            icon:'warning',

            title:'Hapus Dosen?',

            html:`

                Data dosen akan dihapus.

                <br><br>

                <b>Seluruh jadwal dosen ini juga akan terhapus.</b>

            `,

            showCancelButton:true,

            confirmButtonColor:'#dc3545',

            cancelButtonColor:'#64748b',

            confirmButtonText:'Ya, Hapus',

            cancelButtonText:'Batal'

        }).then((result)=>{

            if(result.isConfirmed){

                window.location.href=url;

            }

        });

    });

});

</script>

<?php if($pesan_sukses!=""): ?>

<script>

document.addEventListener('DOMContentLoaded',function(){

    Swal.fire({

        icon:'success',

        title:'Berhasil',

        text:'<?= addslashes($pesan_sukses) ?>',

        timer:2500,

        showConfirmButton:false

    });

});

</script>

<?php endif; ?>

<?php if($pesan_error!=""): ?>

<script>

document.addEventListener('DOMContentLoaded',function(){

    Swal.fire({

        icon:'error',

        title:'Oops...',

        text:'<?= addslashes($pesan_error) ?>'

    });

});

</script>

<?php endif; ?>

<?php if(isset($_SESSION['hapus_sukses'])): ?>

<script>

document.addEventListener('DOMContentLoaded',function(){

    Swal.fire({

        icon:'success',

        title:'Terhapus',

        text:'Data dosen berhasil dihapus',

        timer:2000,

        showConfirmButton:false

    });

});

</script>

<?php
unset($_SESSION['hapus_sukses']);


endif;
?>

