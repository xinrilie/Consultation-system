<?php
session_start();
require_once 'config/koneksi.php';

// Proteksi akses: hanya admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan_error = "";
$pesan_sukses = "";

// PROSES CREATE 
if (isset($_POST['simpan'])) {
    $nip = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama_dosen = mysqli_real_escape_string($koneksi, trim($_POST['nama_dosen']));
    $kategori = mysqli_real_escape_string($koneksi, trim($_POST['kategori_keahlian']));
    $password_input = trim($_POST['password']);
    
    $password_hash = md5($password_input); 

    if (empty($nip) || empty($nama_dosen) || empty($kategori) || empty($password_input)) {
        $pesan_error = "Semua kolom harus diisi!";
    } else {
        $nama_file_foto = 'default.png'; 

        // Proses Foto Hasil Cropper.js (Format Base64)
        if (!empty($_POST['cropped_foto'])) {
            $image_parts = explode(";base64,", $_POST['cropped_foto']);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $nama_file_foto = uniqid() . '.png'; // Hasil crop otomatis disave sebagai PNG
                $lokasi_simpan = 'uploads/' . $nama_file_foto;
                file_put_contents($lokasi_simpan, $image_base64);
            }
        } 
        // Fallback jika tidak dicrop tapi langsung upload biasa
        elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $ekstensi_diizinkan = array('jpg', 'jpeg', 'png');
            if (in_array($file_ext, $ekstensi_diizinkan)) {
                $nama_file_foto = uniqid() . '.' . $file_ext;
                $lokasi_simpan = 'uploads/' . $nama_file_foto;
                move_uploaded_file($file_tmp, $lokasi_simpan);
            } else {
                $pesan_error = "Hanya file JPG, JPEG, dan PNG yang diizinkan!";
            }
        }

        if ($pesan_error == "") {
            $query_tambah = "INSERT INTO dosen (nip, nama_dosen, kategori_keahlian, password, foto) 
                             VALUES ('$nip', '$nama_dosen', '$kategori', '$password_hash', '$nama_file_foto')";
            
            if (mysqli_query($koneksi, $query_tambah)) {
                $pesan_sukses = "Dosen berhasil ditambahkan!";
            } else {
                $pesan_error = "Gagal menambah data: " . mysqli_error($koneksi);
            }
        }
    }
}

// PROSES DELETE
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    if (is_numeric($id_hapus)) {
        $query_foto = mysqli_query($koneksi, "SELECT foto FROM dosen WHERE id_dosen = $id_hapus");
        $data_foto = mysqli_fetch_assoc($query_foto);
        if ($data_foto['foto'] != 'default.png' && file_exists('uploads/' . $data_foto['foto'])) {
            unlink('uploads/' . $data_foto['foto']);
        }

        $query_hapus = "DELETE FROM dosen WHERE id_dosen = $id_hapus";
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Data dosen dihapus.', showConfirmButton: false, timer: 1500 }).then(() => {
                        window.location.href = 'dosen.php';
                    });
                });
            </script>";
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
    <title>Kelola Data Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    
    <style>
        .card-dosen { transition: transform 0.3s; }
        .card-dosen:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .foto-profil {
            width: 120px; height: 120px; object-fit: cover; object-position: center;
            border: 4px solid #fff; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        /* Style area crop gambar */
        .img-container { max-height: 400px; display: flex; justify-content: center; }
        .img-container img { max-width: 100%; display: block; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Sistem Konsultasi</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link text-white" href="dashboard.php">&larr; Kembali</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white fw-bold">Tambah Dosen Baru</div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="formDosen">
                        <div class="mb-3">
                            <label class="form-label">NIP / NIDN</label>
                            <input type="text" name="nip" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama_dosen" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori Keahlian</label>
                            <input type="text" name="kategori_keahlian" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Dosen</label>
                            <input type="password" name="password" class="form-control" placeholder="Buat password..." required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Foto Profil (Opsional)</label>
                            <input type="file" id="inputFoto" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            <small class="text-muted" id="statusFoto">Format: JPG/PNG.</small>
                            <input type="hidden" name="cropped_foto" id="croppedFotoInput">
                        </div>
                        <button type="submit" name="simpan" class="btn btn-success w-100 fw-bold">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                <h4 class="fw-bold text-secondary mb-0">Daftar Dosen</h4>
            </div>

            <div class="row">
                <?php
                $result = mysqli_query($koneksi, "SELECT * FROM dosen ORDER BY id_dosen DESC");
                if(mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        $foto_dosen = (!empty($row['foto'])) ? $row['foto'] : 'default.png';
                ?>
                <div class="col-sm-6 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 card-dosen h-100 pt-4">
                        <div class="text-center">
                            <img src="uploads/<?= $foto_dosen; ?>" class="rounded-circle foto-profil" alt="Foto">
                        </div>
                        <div class="card-body text-center d-flex flex-column">
                            <h5 class="card-title fw-bold text-primary mb-1"><?= $row['nama_dosen']; ?></h5>
                            <p class="card-text text-muted small mb-2"><?= $row['nip']; ?></p>
                            <span class="badge bg-secondary mb-3 mt-auto"><?= $row['kategori_keahlian']; ?></span>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between pb-4 px-3 gap-2">
                            <a href="edit_dosen.php?id=<?= $row['id_dosen']; ?>" class="btn btn-outline-warning btn-sm w-100 fw-bold">Edit</a>
                            <a href="dosen.php?hapus=<?= $row['id_dosen']; ?>" class="btn btn-outline-danger btn-sm w-100 fw-bold btn-hapus">Hapus</a>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="col-12 text-center py-5">
                        <h5 class="text-muted">Belum ada data dosen.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Sesuaikan Foto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container bg-dark">
                    <img id="imageToCrop" src="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold" id="btnCrop">Crop & Simpan Foto</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    <?php if($pesan_sukses != ""): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= $pesan_sukses; ?>', timer: 3000 });
    <?php endif; ?>
    <?php if($pesan_error != ""): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= $pesan_error; ?>' });
    <?php endif; ?>

    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({ title: 'Hapus Dosen?', text: "Data & jadwal dosen ini akan ikut terhapus!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Ya, Hapus!' }).then((result) => { if (result.isConfirmed) { window.location.href = url; } })
        });
    });

    // SCRIPT UNTUK FITUR CROP FOTO
    let cropper;
    const inputFoto = document.getElementById('inputFoto');
    const imageToCrop = document.getElementById('imageToCrop');
    const cropModalElement = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalElement);
    const croppedFotoInput = document.getElementById('croppedFotoInput');
    const statusFoto = document.getElementById('statusFoto');

    // Ketika admin memilih foto
    inputFoto.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imageToCrop.src = event.target.result;
                cropModal.show(); // Munculkan popup
            };
            reader.readAsDataURL(files[0]);
        }
    });

    // Inisialisasi cropper saat modal terbuka
    cropModalElement.addEventListener('shown.bs.modal', function () {
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1, // Paksa kotak 1:1 agar saat dibulatkan pas
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    });

    // Hancurkan cropper saat modal ditutup agar tidak bentrok kalau pilih foto lagi
    cropModalElement.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        // Jika batal crop, bersihkan input file
        if (croppedFotoInput.value === '') {
            inputFoto.value = ''; 
        }
    });

    // Saat tombol "Crop & Simpan" diklik
    document.getElementById('btnCrop').addEventListener('click', function() {
        if (cropper) {
            // Ambil hasil crop dengan ukuran 400x400
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 400
            });
            // Ubah gambar ke format Base64 dan simpan di input hidden
            croppedFotoInput.value = canvas.toDataURL('image/png');
            statusFoto.innerHTML = "<span class='text-success fw-bold'>✔ Foto siap diupload!</span>";
            cropModal.hide(); // Tutup popup
        }
    });
</script>
</body>
</html>