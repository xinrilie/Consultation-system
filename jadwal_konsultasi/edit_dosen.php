<?php
session_start();
require_once 'config/koneksi.php';

// Proteksi akses: hanya admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$id_dosen = $_GET['id'];
$query_data = mysqli_query($koneksi, "SELECT * FROM dosen WHERE id_dosen = '$id_dosen'");
$data_lama = mysqli_fetch_assoc($query_data);

if (!$data_lama) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='dosen.php';</script>";
    exit;
}

$pesan_error = "";
$pesan_sukses = "";

if (isset($_POST['update'])) {
    $nip = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama_dosen = mysqli_real_escape_string($koneksi, trim($_POST['nama_dosen']));
    $kategori = mysqli_real_escape_string($koneksi, trim($_POST['kategori_keahlian']));
    $password_input = trim($_POST['password']);
    
    // Siapkan query dasar
    $query_update = "UPDATE dosen SET nip='$nip', nama_dosen='$nama_dosen', kategori_keahlian='$kategori' ";

    // Jika password diisi, ikut di-update
    if (!empty($password_input)) {
        $password_hash = md5($password_input);
        $query_update .= ", password='$password_hash' ";
    }

    // PROSES FOTO (CROP ATAU UPLOAD BIASA)
    $nama_file_foto = $data_lama['foto']; // Gunakan foto lama sebagai default

    // 1. Jika ada hasil crop (Base64)
    if (!empty($_POST['cropped_foto'])) {
        $image_parts = explode(";base64,", $_POST['cropped_foto']);
        if (count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
            $nama_file_foto = uniqid() . '.png';
            $lokasi_simpan = 'uploads/' . $nama_file_foto;
            
            // Hapus foto lama jika bukan default
            if ($data_lama['foto'] != 'default.png' && file_exists('uploads/' . $data_lama['foto'])) {
                unlink('uploads/' . $data_lama['foto']);
            }
            file_put_contents($lokasi_simpan, $image_base64);
            $query_update .= ", foto='$nama_file_foto' ";
        }
    } 
    // 2. Atau jika upload file biasa (Fallback)
    elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_name = $_FILES['foto']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $ekstensi_diizinkan = array('jpg', 'jpeg', 'png');
        
        if (in_array($file_ext, $ekstensi_diizinkan)) {
            $nama_file_foto = uniqid() . '.' . $file_ext;
            $lokasi_simpan = 'uploads/' . $nama_file_foto;
            
            if (move_uploaded_file($file_tmp, $lokasi_simpan)) {
                if ($data_lama['foto'] != 'default.png' && file_exists('uploads/' . $data_lama['foto'])) {
                    unlink('uploads/' . $data_lama['foto']);
                }
                $query_update .= ", foto='$nama_file_foto' ";
            }
        } else {
            $pesan_error = "Hanya file JPG, JPEG, dan PNG yang diizinkan!";
        }
    }

    $query_update .= " WHERE id_dosen='$id_dosen'";

    if ($pesan_error == "") {
        if (mysqli_query($koneksi, $query_update)) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data dosen diperbarui.', showConfirmButton: false, timer: 1500 }).then(() => {
                        window.location.href = 'dosen.php';
                    });
                });
            </script>";
            exit;
        } else {
            $pesan_error = "Gagal mengupdate data: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <style>
        .foto-preview { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); }
        .img-container { max-height: 400px; display: flex; justify-content: center; background: #eee; }
        .img-container img { max-width: 100%; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Konsultasi</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning fw-bold">Edit Data Dosen</div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="formEdit">
                        <div class="text-center mb-3">
                            <img src="uploads/<?= (!empty($data_lama['foto'])) ? $data_lama['foto'] : 'default.png'; ?>" class="foto-preview" id="previewFoto">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">NIP / NIDN</label>
                            <input type="text" name="nip" class="form-control" value="<?= $data_lama['nip']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama_dosen" class="form-control" value="<?= $data_lama['nama_dosen']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori Keahlian</label>
                            <input type="text" name="kategori_keahlian" class="form-control" value="<?= $data_lama['kategori_keahlian']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-danger">Reset Password (Opsional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin ganti password">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-primary">Ganti Foto (Opsional)</label>
                            <input type="file" id="inputFoto" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            <input type="hidden" name="cropped_foto" id="croppedFotoInput">
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="dosen.php" class="btn btn-secondary w-100 me-2">Batal</a>
                            <button type="submit" name="update" class="btn btn-warning w-100 fw-bold">Update Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Crop Foto Dosen</h5>
            </div>
            <div class="modal-body p-0">
                <div class="img-container"><img id="imageToCrop"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold" id="btnCrop">Crop & Gunakan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    <?php if($pesan_error != ""): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= $pesan_error; ?>' });
    <?php endif; ?>

    let cropper;
    const inputFoto = document.getElementById('inputFoto');
    const imageToCrop = document.getElementById('imageToCrop');
    const cropModalElement = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalElement);
    const croppedInput = document.getElementById('croppedFotoInput');
    const preview = document.getElementById('previewFoto');

    inputFoto.addEventListener('change', (e) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = (event) => {
                imageToCrop.src = event.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(files[0]);
        }
    });

    cropModalElement.addEventListener('shown.bs.modal', () => {
        cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1 });
    });

    cropModalElement.addEventListener('hidden.bs.modal', () => {
        if(cropper) { cropper.destroy(); }
    });

    document.getElementById('btnCrop').addEventListener('click', () => {
        const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
        croppedInput.value = canvas.toDataURL('image/png');
        preview.src = canvas.toDataURL('image/png');
        cropModal.hide();
    });
</script>
</body>
</html>