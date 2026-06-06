<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan_sukses = "";
$pesan_error  = "";

/* ==========================
   TAMBAH DOSEN
========================== */
if (isset($_POST['tambah'])) {

    $nip         = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama_dosen  = mysqli_real_escape_string($koneksi, trim($_POST['nama_dosen']));
    $kategori    = mysqli_real_escape_string($koneksi, trim($_POST['kategori_keahlian']));

    if (empty($nip) || empty($nama_dosen) || empty($kategori)) {
        $pesan_error = "Semua field wajib diisi.";
    } else {
        $cek = mysqli_query($koneksi, "SELECT * FROM dosen WHERE nip='$nip'");

        if (mysqli_num_rows($cek) > 0) {
            $pesan_error = "NIP sudah terdaftar.";
        } else {
            $password       = md5('12345');
            $nama_file_foto = 'default.png';

            // Proses foto hasil Cropper.js (Base64)
            if (!empty($_POST['cropped_foto'])) {
                $image_parts = explode(";base64,", $_POST['cropped_foto']);
                if (count($image_parts) == 2) {
                    $image_base64   = base64_decode($image_parts[1]);
                    $nama_file_foto = uniqid() . '.png';
                    file_put_contents('uploads/' . $nama_file_foto, $image_base64);
                }
            }
            // Fallback: upload biasa tanpa crop
            elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                    $nama_file_foto = uniqid() . '.' . $file_ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $nama_file_foto);
                } else {
                    $pesan_error = "Format foto harus JPG atau PNG.";
                }
            }

            if ($pesan_error == "") {
                $insert = mysqli_query($koneksi,
                    "INSERT INTO dosen (nip, nama_dosen, kategori_keahlian, password, foto)
                     VALUES ('$nip','$nama_dosen','$kategori','$password','$nama_file_foto')"
                );
                $pesan_sukses = $insert ? "Dosen berhasil ditambahkan." : "Gagal menambahkan dosen.";
                if (!$insert) $pesan_error = "Gagal menambahkan dosen.";
            }
        }
    }
}

/* ==========================
   UPDATE DOSEN
========================== */
if (isset($_POST['update'])) {

    $id_dosen    = intval($_POST['id_dosen']);
    $nip         = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama_dosen  = mysqli_real_escape_string($koneksi, trim($_POST['nama_dosen']));
    $kategori    = mysqli_real_escape_string($koneksi, trim($_POST['kategori_keahlian']));
    $password_baru = trim($_POST['password']);

    $cek = mysqli_query($koneksi,
        "SELECT * FROM dosen WHERE nip='$nip' AND id_dosen!='$id_dosen'"
    );

    if (mysqli_num_rows($cek) > 0) {
        $pesan_error = "NIP sudah digunakan dosen lain.";
    } else {
        mysqli_query($koneksi,
            "UPDATE dosen SET nip='$nip', nama_dosen='$nama_dosen',
             kategori_keahlian='$kategori' WHERE id_dosen='$id_dosen'"
        );

        if (!empty($password_baru)) {
            mysqli_query($koneksi,
                "UPDATE dosen SET password='" . md5($password_baru) . "'
                 WHERE id_dosen='$id_dosen'"
            );
        }

        // Update foto jika ada hasil crop baru
        if (!empty($_POST['cropped_foto_edit'])) {
            $old = mysqli_fetch_assoc(
                mysqli_query($koneksi, "SELECT foto FROM dosen WHERE id_dosen='$id_dosen'")
            );
            if ($old && $old['foto'] != 'default.png' && file_exists('uploads/' . $old['foto'])) {
                unlink('uploads/' . $old['foto']);
            }
            $parts = explode(";base64,", $_POST['cropped_foto_edit']);
            if (count($parts) == 2) {
                $new_foto = uniqid() . '.png';
                file_put_contents('uploads/' . $new_foto, base64_decode($parts[1]));
                mysqli_query($koneksi,
                    "UPDATE dosen SET foto='$new_foto' WHERE id_dosen='$id_dosen'"
                );
            }
        }

        $pesan_sukses = "Data dosen berhasil diperbarui.";
    }
}

/* ==========================
   HAPUS DOSEN
========================== */
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    $q = mysqli_query($koneksi, "SELECT foto FROM dosen WHERE id_dosen='$id'");
    $d = mysqli_fetch_assoc($q);
    if ($d && $d['foto'] != 'default.png' && file_exists('uploads/' . $d['foto'])) {
        unlink('uploads/' . $d['foto']);
    }

    mysqli_query($koneksi, "DELETE FROM jadwal WHERE id_dosen='$id'");
    mysqli_query($koneksi, "DELETE FROM dosen WHERE id_dosen='$id'");

    $_SESSION['hapus_sukses'] = true;
    header("Location: dosen.php");
    exit;
}

/* ==========================
   SEARCH
========================== */
$cari = "";
if (isset($_GET['cari'])) {
    $cari = mysqli_real_escape_string($koneksi, trim($_GET['cari']));
}

/* ==========================
   TOTAL DOSEN
========================== */
$total_dosen = mysqli_num_rows(
    mysqli_query($koneksi, "SELECT * FROM dosen")
);

/* ==========================
   DATA DOSEN
========================== */
$query_dosen = mysqli_query($koneksi,
    "SELECT * FROM dosen
     WHERE nip LIKE '%$cari%'
     OR nama_dosen LIKE '%$cari%'
     OR kategori_keahlian LIKE '%$cari%'
     ORDER BY id_dosen DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Data Dosen</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
:root {
    --green-start : #10b981;
    --green-end   : #34d399;
    --grad        : linear-gradient(135deg, var(--green-start), var(--green-end));
    --dark        : #0f172a;
    --light-bg    : #f1f5f9;
}

/* ── GLOBAL ─────────────────────────────── */
body {
    background: var(--light-bg);
    font-family: 'Segoe UI', sans-serif;
}

/* ── NAVBAR ──────────────────────────────── */
.navbar-custom {
    background: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,.05);
}
.brand-logo {
    width: 42px; height: 42px;
    border-radius: 12px;
    background: var(--grad);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
}

/* ── HERO ────────────────────────────────── */
.hero-banner {
    background: var(--grad);
    color: #fff;
    padding: 35px;
    border-radius: 28px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 15px 40px rgba(16,185,129,.20);
}
.hero-banner::before {
    content: "";
    position: absolute;
    width: 240px; height: 240px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    right: -80px; top: -80px;
}
.hero-banner h2 { font-weight: 700; }

/* ── STATS ───────────────────────────────── */
.stats-card {
    border: none; border-radius: 24px;
    background: #fff;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
}
.stats-icon {
    width: 70px; height: 70px;
    border-radius: 20px;
    background: var(--grad);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
}

/* ── CARDS ───────────────────────────────── */
.main-card {
    border: none; border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.05);
}
.card-header-modern {
    background: #fff; border: none;
    padding: 20px 25px; font-weight: 700;
}

/* ── FORM ────────────────────────────────── */
.form-control {
    border-radius: 14px;
    min-height: 52px;
    border: 1px solid #dbe2ea;
}
.form-control:focus {
    border-color: var(--green-start);
    box-shadow: 0 0 0 4px rgba(16,185,129,.12);
}
.form-control-file-sm {
    min-height: auto !important;
    padding: 9px 14px !important;
    font-size: 14px;
}
.btn-save {
    border: none; height: 52px;
    border-radius: 14px; font-weight: 600;
    background: var(--grad);
    transition: opacity .2s;
}
.btn-save:hover { opacity: .88; }

.alert-default-pass {
    background: linear-gradient(135deg,rgba(16,185,129,.08),rgba(52,211,153,.08));
    border: 1px solid rgba(16,185,129,.25);
    border-radius: 14px; color: #065f46;
    font-size: 14px;
}

/* ── DOSEN CARD ──────────────────────────── */
.dosen-card {
    border: none; border-radius: 24px;
    background: #fff;
    box-shadow: 0 6px 20px rgba(0,0,0,.06);
    transition: transform .3s, box-shadow .3s;
}
.dosen-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(16,185,129,.15);
}
.foto-profil {
    width: 100px; height: 100px;
    object-fit: cover; object-position: center;
    border-radius: 50%;
    border: 4px solid #f0fdf4;
    box-shadow: 0 4px 15px rgba(16,185,129,.20);
}
.foto-profil-sm {
    width: 72px; height: 72px;
    object-fit: cover; object-position: center;
    border-radius: 50%;
    border: 3px solid #f0fdf4;
    box-shadow: 0 3px 10px rgba(16,185,129,.20);
}
.badge-keahlian {
    background: var(--grad);
    color: #fff;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 12px; font-weight: 600;
    display: inline-block;
}

/* ── SEARCH ──────────────────────────────── */
.search-box { position: relative; }
.search-box i { position: absolute; left: 15px; top: 16px; color: #94a3b8; }
.search-box input { padding-left: 45px; }

/* ── CROP MODAL ──────────────────────────── */
.img-container {
    max-height: 380px;
    display: flex; justify-content: center;
    overflow: hidden;
}
.img-container img { max-width: 100%; display: block; }

/* ── z-index fix for stacked modals ──────── */
.modal-backdrop.show + .modal-backdrop.show { z-index: 1065; }
#cropModal,
#cropModalEdit { z-index: 1070 !important; }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════
     NAVBAR
══════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
            <div class="brand-logo">
                <i class="bi bi-person-workspace"></i>
            </div>
            <strong>Kelola Dosen</strong>
        </a>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>
</nav>

<div class="container pb-5">

    <!-- ═══ HERO ═══ -->
    <div class="hero-banner mb-4 animate__animated animate__fadeInDown">
        <h2><i class="bi bi-mortarboard-fill me-2"></i>Data Dosen</h2>
        <p class="mb-0 opacity-75">Kelola seluruh data dosen, keahlian dan akun dosen dalam satu halaman.</p>
    </div>

    <!-- ═══ STAT ═══ -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card stats-card">
                <div class="card-body d-flex align-items-center py-3">
                    <div class="stats-icon me-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Dosen Terdaftar</small>
                        <h3 class="mb-0 fw-bold"><?= $total_dosen ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- ════════════════════════
             FORM TAMBAH DOSEN
        ════════════════════════ -->
        <div class="col-lg-4">
            <div class="card main-card sticky-top" style="top:20px">
                <div class="card-header card-header-modern border-bottom">
                    <i class="bi bi-person-plus-fill text-success me-2"></i>Tambah Dosen Baru
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" id="formTambah">

                        <div class="mb-3">
                            <label class="fw-semibold small text-muted text-uppercase mb-1">NIP / NIDN</label>
                            <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP..." required>
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold small text-muted text-uppercase mb-1">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama_dosen" class="form-control" placeholder="Masukkan nama dosen..." required>
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold small text-muted text-uppercase mb-1">Kategori Keahlian</label>
                            <input type="text" name="kategori_keahlian" class="form-control" placeholder="Contoh: Teknik Informatika..." required>
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold small text-muted text-uppercase mb-1">
                                Foto Profil
                                <span class="fw-normal text-muted">(Opsional)</span>
                            </label>
                            <input type="file" id="inputFoto" name="foto"
                                   class="form-control form-control-file-sm"
                                   accept="image/png,image/jpeg,image/jpg">
                            <div id="statusFoto">
                                <small class="text-muted">Format: JPG / PNG. Akan dipotong otomatis.</small>
                            </div>
                            <input type="hidden" name="cropped_foto" id="croppedFotoInput">
                        </div>

                        <div class="alert alert-default-pass mb-3 py-2">
                            <i class="bi bi-key-fill me-1"></i>
                            Password default: <strong>12345</strong>
                        </div>

                        <button type="submit" name="tambah" class="btn btn-save text-white w-100">
                            <i class="bi bi-plus-circle-fill me-2"></i>Tambah Dosen
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <!-- ════════════════════════
             DAFTAR DOSEN (CARDS)
        ════════════════════════ -->
        <div class="col-lg-8">
            <div class="card main-card">

                <div class="card-header card-header-modern border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <span>
                            <i class="bi bi-grid-3x3-gap-fill text-success me-2"></i>
                            Daftar Dosen
                        </span>
                        <form method="GET" class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" name="cari" class="form-control"
                                   style="min-height:44px; font-size:14px;"
                                   placeholder="Cari NIP, Nama atau Keahlian..."
                                   value="<?= htmlspecialchars($cari) ?>">
                        </form>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">

                        <?php
                        if (mysqli_num_rows($query_dosen) > 0):
                        while ($row = mysqli_fetch_assoc($query_dosen)):
                            $foto_src = (!empty($row['foto']) && $row['foto'] != 'default.png')
                                ? 'uploads/' . $row['foto']
                                : 'https://ui-avatars.com/api/?name=' . urlencode($row['nama_dosen'])
                                  . '&background=10b981&color=fff&size=200&rounded=true';
                        ?>

                        <div class="col-sm-6">
                            <div class="dosen-card h-100 p-4 text-center">

                                <img src="<?= $foto_src ?>"
                                     class="foto-profil mb-3"
                                     alt="Foto <?= htmlspecialchars($row['nama_dosen']) ?>"
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['nama_dosen']) ?>&background=10b981&color=fff&size=200'">

                                <h6 class="fw-bold text-primary mb-1">
                                    <?= htmlspecialchars($row['nama_dosen']) ?>
                                </h6>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars($row['nip']) ?>
                                </p>
                                <span class="badge-keahlian mb-3">
                                    <?= htmlspecialchars($row['kategori_keahlian']) ?>
                                </span>

                                <div class="d-flex gap-2 justify-content-center mt-2">
                                    <button class="btn btn-outline-warning btn-sm fw-semibold px-3"
                                            style="border-radius:10px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal<?= $row['id_dosen'] ?>">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    <a href="?hapus=<?= $row['id_dosen'] ?>"
                                       class="btn btn-outline-danger btn-sm btn-hapus fw-semibold px-3"
                                       style="border-radius:10px;">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </a>
                                </div>

                            </div>
                        </div>

                        <!-- ════ MODAL EDIT ════ -->
                        <div class="modal fade" id="editModal<?= $row['id_dosen'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content border-0 rounded-4">

                                    <form method="POST" id="formEdit<?= $row['id_dosen'] ?>">
                                        <input type="hidden" name="id_dosen" value="<?= $row['id_dosen'] ?>">

                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-pencil-square text-success me-2"></i>
                                                Edit Dosen
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body px-4">

                                            <!-- Preview + ganti foto -->
                                            <div class="text-center mb-3">
                                                <img src="<?= $foto_src ?>"
                                                     class="foto-profil-sm mb-2"
                                                     id="fotoPreview<?= $row['id_dosen'] ?>"
                                                     alt="Foto">
                                                <div>
                                                    <input type="file"
                                                           class="form-control form-control-file-sm inputFotoEdit"
                                                           id="inputFotoEdit<?= $row['id_dosen'] ?>"
                                                           data-id="<?= $row['id_dosen'] ?>"
                                                           accept="image/png,image/jpeg,image/jpg">
                                                    <div id="statusFotoEdit<?= $row['id_dosen'] ?>">
                                                        <small class="text-muted">Kosongkan jika tidak ganti foto.</small>
                                                    </div>
                                                    <input type="hidden" name="cropped_foto_edit"
                                                           id="croppedFotoEditInput<?= $row['id_dosen'] ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="fw-semibold small">NIP</label>
                                                <input type="text" name="nip" class="form-control"
                                                       value="<?= htmlspecialchars($row['nip']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-semibold small">Nama Dosen</label>
                                                <input type="text" name="nama_dosen" class="form-control"
                                                       value="<?= htmlspecialchars($row['nama_dosen']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-semibold small">Keahlian</label>
                                                <input type="text" name="kategori_keahlian" class="form-control"
                                                       value="<?= htmlspecialchars($row['kategori_keahlian']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="fw-semibold small">Password Baru</label>
                                                <input type="password" name="password" class="form-control"
                                                       placeholder="Kosongkan jika tidak ganti...">
                                            </div>

                                        </div>

                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update"
                                                    class="btn btn-success fw-semibold">
                                                <i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                        <!-- ════ END MODAL EDIT ════ -->

                        <?php endwhile;
                        else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                            <span class="text-muted">
                                <?= $cari ? "Tidak ada hasil pencarian untuk \"" . htmlspecialchars($cari) . "\"." : "Belum ada data dosen." ?>
                            </span>
                        </div>
                        <?php endif; ?>

                    </div><!-- /row -->
                </div><!-- /card-body -->
            </div><!-- /card -->
        </div>

    </div><!-- /row g-4 -->
</div><!-- /container -->


<!-- ═══════════════════════════════════════
     CROP MODAL — TAMBAH DOSEN
══════════════════════════════════════════ -->
<div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white border-0" style="background:var(--grad)">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-crop me-2"></i>Sesuaikan Foto Profil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container bg-dark">
                    <img id="imageToCrop" src="" alt="Crop">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold" id="btnCrop">
                    <i class="bi bi-scissors me-1"></i>Crop & Gunakan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════
     CROP MODAL — EDIT DOSEN
══════════════════════════════════════════ -->
<div class="modal fade" id="cropModalEdit" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white border-0" style="background:var(--grad)">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-crop me-2"></i>Sesuaikan Foto Profil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container bg-dark">
                    <img id="imageToCropEdit" src="" alt="Crop Edit">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success fw-bold" id="btnCropEdit">
                    <i class="bi bi-scissors me-1"></i>Crop & Gunakan Foto
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ═══ SCRIPTS ══════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
/* ──────────────────────────────────────────
   SEARCH: auto submit setelah 500ms berhenti
────────────────────────────────────────── */
const searchInput = document.querySelector('input[name="cari"]');
if (searchInput) {
    searchInput.addEventListener('keyup', function () {
        clearTimeout(window.searchTimer);
        window.searchTimer = setTimeout(() => this.form.submit(), 500);
    });
}

/* ──────────────────────────────────────────
   KONFIRMASI HAPUS
────────────────────────────────────────── */
document.querySelectorAll('.btn-hapus').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Dosen?',
            html: 'Data dosen akan dihapus.<br><br><b>Seluruh jadwal dosen ini juga akan ikut terhapus.</b>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) window.location.href = url;
        });
    });
});

/* ──────────────────────────────────────────
   LOADING SAAT SUBMIT (hanya form tambah & edit)
────────────────────────────────────────── */
document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', () => {
        Swal.fire({
            title: 'Menyimpan Data',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    });
});

/* ══════════════════════════════════════════
   CROPPER.JS — FORM TAMBAH DOSEN
══════════════════════════════════════════ */
let cropper = null;
const inputFoto      = document.getElementById('inputFoto');
const imageToCrop    = document.getElementById('imageToCrop');
const cropModalEl    = document.getElementById('cropModal');
const cropModal      = new bootstrap.Modal(cropModalEl);
const croppedInput   = document.getElementById('croppedFotoInput');
const statusFoto     = document.getElementById('statusFoto');

inputFoto.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        imageToCrop.src = ev.target.result;
        cropModal.show();
    };
    reader.readAsDataURL(file);
});

cropModalEl.addEventListener('shown.bs.modal', () => {
    cropper = new Cropper(imageToCrop, {
        aspectRatio: 1, viewMode: 1, dragMode: 'move',
        autoCropArea: 1, restore: false, guides: true, center: true,
        highlight: false, cropBoxMovable: true, cropBoxResizable: true,
        toggleDragModeOnDblclick: false
    });
});

cropModalEl.addEventListener('hidden.bs.modal', () => {
    if (cropper) { cropper.destroy(); cropper = null; }
    if (!croppedInput.value) inputFoto.value = '';
});

document.getElementById('btnCrop').addEventListener('click', () => {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
    croppedInput.value = canvas.toDataURL('image/png');
    statusFoto.innerHTML = "<small class='text-success fw-bold'><i class='bi bi-check-circle-fill me-1'></i>Foto siap diupload!</small>";
    cropModal.hide();
});

/* ══════════════════════════════════════════
   CROPPER.JS — FORM EDIT DOSEN
══════════════════════════════════════════ */
let cropperEdit    = null;
let activeEditId   = null;
const imageToCropEdit  = document.getElementById('imageToCropEdit');
const cropModalEditEl  = document.getElementById('cropModalEdit');
const cropModalEdit    = new bootstrap.Modal(cropModalEditEl);

// Setiap input foto di modal edit
document.querySelectorAll('.inputFotoEdit').forEach(input => {
    input.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        activeEditId = this.dataset.id;
        const reader = new FileReader();
        reader.onload = ev => {
            imageToCropEdit.src = ev.target.result;
            // Tutup dulu edit modal, buka crop modal
            bootstrap.Modal.getInstance(
                document.getElementById('editModal' + activeEditId)
            ).hide();
            setTimeout(() => cropModalEdit.show(), 300);
        };
        reader.readAsDataURL(file);
    });
});

cropModalEditEl.addEventListener('shown.bs.modal', () => {
    cropperEdit = new Cropper(imageToCropEdit, {
        aspectRatio: 1, viewMode: 1, dragMode: 'move',
        autoCropArea: 1, restore: false, guides: true, center: true,
        highlight: false, cropBoxMovable: true, cropBoxResizable: true,
        toggleDragModeOnDblclick: false
    });
});

cropModalEditEl.addEventListener('hidden.bs.modal', () => {
    if (cropperEdit) { cropperEdit.destroy(); cropperEdit = null; }
    // Kembalikan ke edit modal
    if (activeEditId) {
        setTimeout(() => {
            new bootstrap.Modal(
                document.getElementById('editModal' + activeEditId)
            ).show();
        }, 200);
    }
});

document.getElementById('btnCropEdit').addEventListener('click', () => {
    if (!cropperEdit || !activeEditId) return;
    const canvas = cropperEdit.getCroppedCanvas({ width: 400, height: 400 });
    const dataUrl = canvas.toDataURL('image/png');

    // Simpan ke hidden input & update preview
    document.getElementById('croppedFotoEditInput' + activeEditId).value = dataUrl;
    document.getElementById('fotoPreview' + activeEditId).src = dataUrl;
    document.getElementById('statusFotoEdit' + activeEditId).innerHTML =
        "<small class='text-success fw-bold'><i class='bi bi-check-circle-fill me-1'></i>Foto baru siap disimpan!</small>";

    cropModalEdit.hide();
});
</script>

<!-- ═══ SWEETALERT NOTIFIKASI ═══════════ -->
<?php if ($pesan_sukses != ""): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'success', title: 'Berhasil!',
        text: '<?= addslashes($pesan_sukses) ?>',
        timer: 2500, showConfirmButton: false
    });
});
</script>
<?php endif; ?>

<?php if ($pesan_error != ""): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'error', title: 'Oops...',
        text: '<?= addslashes($pesan_error) ?>'
    });
});
</script>
<?php endif; ?>

<?php if (isset($_SESSION['hapus_sukses'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon: 'success', title: 'Terhapus!',
        text: 'Data dosen berhasil dihapus.',
        timer: 2000, showConfirmButton: false
    });
});
</script>
<?php unset($_SESSION['hapus_sukses']); endif; ?>

</body>
</html>
