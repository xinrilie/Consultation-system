<?php
session_start();
require_once 'config/koneksi.php';

if (
    !isset($_SESSION['id_dosen']) ||
    $_SESSION['role'] != 'dosen'
){
    header("Location: dashboard.php");
    exit;
}

$id_dosen = $_SESSION['id_dosen'];

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM dosen
     WHERE id_dosen='$id_dosen'"
);

$dosen = mysqli_fetch_assoc($query);

// Proses update
if(isset($_POST['simpan']))
{
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_dosen']);
    $keahlian = mysqli_real_escape_string($koneksi, $_POST['kategori_keahlian']);

    // Jika upload foto baru
    if($_FILES['foto']['name'] != '')
    {
        $namaFile = uniqid() . "_" . $_FILES['foto']['name'];
        $tmp = $_FILES['foto']['tmp_name'];

        move_uploaded_file(
            $tmp,
            "uploads/" . $namaFile
        );

        mysqli_query(
            $koneksi,
            "UPDATE dosen SET
            nama_dosen='$nama',
            kategori_keahlian='$keahlian',
            foto='$namaFile'
            WHERE id_dosen='$id_dosen'"
        );
    }
    else
    {
        mysqli_query(
            $koneksi,
            "UPDATE dosen SET
            nama_dosen='$nama',
            kategori_keahlian='$keahlian'
            WHERE id_dosen='$id_dosen'"
        );
    }

   $update = mysqli_query(
    $koneksi,
    "UPDATE dosen SET
    nip='$nip',
    nama_dosen='$nama',
    kategori_keahlian='$keahlian'
    WHERE id_dosen='$id_dosen'"
);

if($update)
{
    $_SESSION['profil_updated'] = true;

    header("Location: profil_dosen.php");
    exit;
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Dosen</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4>Profil Dosen</h4>
        </div>

        <div class="card-body">

            <div class="text-center mb-4">

                <?php if(!empty($dosen['foto'])) : ?>

                    <img
                        src="uploads/<?php echo $dosen['foto']; ?>"
                        width="150"
                        height="150"
                        class="rounded-circle border">

                <?php else : ?>

                    <img
                        src="https://via.placeholder.com/150"
                        width="150"
                        height="150"
                        class="rounded-circle border">

                <?php endif; ?>

            </div>

            <form method="POST"
                  enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">
                        NIP
                    </label>

                    <input
                        type="text"
                        name="nip"
                        class="form-control"
                        value="<?php echo $dosen['nip']; ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Nama Dosen
                    </label>

                    <input
                        type="text"
                        name="nama_dosen"
                        class="form-control"
                        value="<?php echo $dosen['nama_dosen']; ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Bidang Keahlian
                    </label>

                    <input
                        type="text"
                        name="kategori_keahlian"
                        class="form-control"
                        value="<?php echo $dosen['kategori_keahlian']; ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Foto Profil
                    </label>

                    <input
                        type="file"
                        name="foto"
                        class="form-control">
                </div>

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-success">

                    Simpan Perubahan

                </button>

                <a href="dashboard.php"
                   class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if(isset($_SESSION['profil_updated'])): ?>

<script>

document.addEventListener('DOMContentLoaded', function(){

    Swal.fire({

        toast: true,

        position: 'top-end',

        icon: 'success',

        title: 'Profil berhasil diperbarui',

        showConfirmButton: false,

        timer: 3000,

        timerProgressBar: true

    });

});

</script>

<?php
unset($_SESSION['profil_updated']);
endif;
?>
</body>
</html>
