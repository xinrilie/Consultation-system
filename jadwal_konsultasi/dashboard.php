<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

$nama_user = $_SESSION['nama_mhs'];
$role      = $_SESSION['role'];

$pesan_sukses_profil = "";
$pesan_error_profil  = "";

/* ==========================
   UPDATE PROFIL DOSEN
========================== */
if (isset($_POST['update_profil']) && $role == 'dosen') {

    $id_dosen  = intval($_SESSION['id_user']);
    $nip       = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama_dosen']));
    $kategori  = mysqli_real_escape_string($koneksi, trim($_POST['kategori_keahlian']));
    $pass_baru = trim($_POST['password']);

    mysqli_query($koneksi,
        "UPDATE dosen SET nip='$nip', nama_dosen='$nama',
         kategori_keahlian='$kategori' WHERE id_dosen='$id_dosen'"
    );

    if (!empty($pass_baru)) {
        mysqli_query($koneksi,
            "UPDATE dosen SET password='" . md5($pass_baru) . "'
             WHERE id_dosen='$id_dosen'"
        );
    }

    if (!empty($_POST['cropped_foto_profil'])) {
        $old = mysqli_fetch_assoc(
            mysqli_query($koneksi, "SELECT foto FROM dosen WHERE id_dosen='$id_dosen'")
        );
        if ($old && $old['foto'] != 'default.png' && file_exists('uploads/' . $old['foto'])) {
            unlink('uploads/' . $old['foto']);
        }
        $parts = explode(";base64,", $_POST['cropped_foto_profil']);
        if (count($parts) == 2) {
            $new_foto = uniqid() . '.png';
            file_put_contents('uploads/' . $new_foto, base64_decode($parts[1]));
            mysqli_query($koneksi, "UPDATE dosen SET foto='$new_foto' WHERE id_dosen='$id_dosen'");
        }
    }

    $_SESSION['nama_mhs']      = $nama;
    $nama_user                 = $nama;
    $pesan_sukses_profil       = "Profil berhasil diperbarui.";
}

/* ==========================
   FETCH DATA DOSEN
========================== */
$dosen_data      = null;
$foto_profil_src = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user)
                   . '&background=4f46e5&color=fff&size=200&rounded=true';

if ($role == 'dosen') {
    $dosen_data = mysqli_fetch_assoc(
        mysqli_query($koneksi,
            "SELECT * FROM dosen WHERE id_dosen='" . intval($_SESSION['id_user']) . "'"
        )
    );
    if ($dosen_data && !empty($dosen_data['foto']) && $dosen_data['foto'] != 'default.png') {
        $foto_profil_src = 'uploads/' . $dosen_data['foto'];
    } elseif ($dosen_data) {
        $foto_profil_src = 'https://ui-avatars.com/api/?name=' . urlencode($dosen_data['nama_dosen'])
                           . '&background=10b981&color=fff&size=200&rounded=true';
    }
}

/* ==========================
   STATISTIK (admin)
========================== */
$total_dosen    = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM dosen"));
$total_mahasiswa = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM users WHERE role='mahasiswa'"));
$total_jadwal   = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM jadwal"));
$total_menunggu = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM jadwal WHERE status='pending'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Sistem Konsultasi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
:root {
    --primary   : #4f46e5;
    --secondary : #7c3aed;
    --success   : #10b981;
    --info      : #06b6d4;
    --dark      : #0f172a;
    --light     : #f8fafc;
}

body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }

/* ── NAVBAR ──────────────────────────── */
.navbar-custom {
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 3px 20px rgba(0,0,0,.05);
}
.brand-logo {
    width: 42px; height: 42px; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white; display: flex; align-items: center; justify-content: center;
}
.brand-text { font-weight: 700; color: var(--dark); }

/* ── HERO ────────────────────────────── */
.hero-banner {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 28px; padding: 40px; color: white;
    overflow: hidden; position: relative;
    box-shadow: 0 15px 40px rgba(79,70,229,.25);
}
.hero-banner::before {
    content: ""; position: absolute;
    right: -50px; top: -50px; width: 220px; height: 220px;
    border-radius: 50%; background: rgba(255,255,255,.08);
}
.hero-banner::after {
    content: ""; position: absolute;
    right: 40px; bottom: -70px; width: 180px; height: 180px;
    border-radius: 50%; background: rgba(255,255,255,.08);
}
.hero-banner h2 { font-weight: 700; }

/* foto di hero banner dosen */
.hero-foto {
    width: 90px; height: 90px; border-radius: 50%;
    object-fit: cover; object-position: center;
    border: 4px solid rgba(255,255,255,.4);
    box-shadow: 0 8px 25px rgba(0,0,0,.2);
    position: relative; z-index: 1;
}

/* ── STATS ───────────────────────────── */
.stats-card {
    border: none; border-radius: 24px; overflow: hidden;
    background: white; box-shadow: 0 10px 25px rgba(0,0,0,.06); transition: .3s;
}
.stats-card:hover { transform: translateY(-5px); box-shadow: 0 20px 35px rgba(79,70,229,.12); }
.stats-icon {
    width: 70px; height: 70px; border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 28px;
}
.bg-purple { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.bg-green  { background: linear-gradient(135deg, #10b981, #34d399); }
.bg-blue   { background: linear-gradient(135deg, #06b6d4, #3b82f6); }
.bg-orange { background: linear-gradient(135deg, #f59e0b, #f97316); }

/* ── DASHBOARD CARD ──────────────────── */
.dashboard-card {
    border: none; border-radius: 25px; overflow: hidden;
    background: white; box-shadow: 0 10px 25px rgba(0,0,0,.06); transition: .35s;
}
.dashboard-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(79,70,229,.15); }
.card-icon {
    width: 90px; height: 90px; border-radius: 25px; margin: auto;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 38px;
}
.icon-primary { background: linear-gradient(135deg,#6366f1,#8b5cf6); }
.icon-success { background: linear-gradient(135deg,#10b981,#34d399); }
.icon-info    { background: linear-gradient(135deg,#06b6d4,#3b82f6); }
.icon-dark    { background: linear-gradient(135deg,#334155,#0f172a); }
.icon-profile { background: linear-gradient(135deg,#f59e0b,#ef4444); }
.dashboard-card .btn { border-radius: 14px; font-weight: 600; }

/* ── SIDEBAR (OFFCANVAS) ─────────────── */
.menu-link {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 15px; border-radius: 12px;
    text-decoration: none; color: #334155; transition: .3s;
}
.menu-link:hover  { background: #eef2ff; color: var(--primary); }
.menu-link.active { background: #eef2ff; color: var(--primary); font-weight: 600; }

/* foto di sidebar */
.sidebar-foto {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover; object-position: center;
    border: 3px solid #e0e7ff;
    box-shadow: 0 4px 15px rgba(79,70,229,.15);
}
.sidebar-user-card {
    background: linear-gradient(135deg, #f0f4ff, #f8fafc);
    border-radius: 18px; padding: 16px 12px;
    margin-bottom: 8px; text-align: center;
}
.sidebar-role-badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white; padding: 3px 12px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
}

/* ── CLOCK ───────────────────────────── */
.clock-badge {
    background: white; padding: 10px 18px; border-radius: 14px;
    box-shadow: 0 5px 15px rgba(0,0,0,.05); font-weight: 600;
}

/* ── FORM PROFIL ─────────────────────── */
.form-control-profil {
    border-radius: 14px; min-height: 50px;
    border: 1px solid #dbe2ea; font-size: 14px;
}
.form-control-profil:focus {
    border-color: var(--success);
    box-shadow: 0 0 0 4px rgba(16,185,129,.12);
}

/* ── CROP MODAL ──────────────────────── */
.img-container { max-height: 350px; display: flex; justify-content: center; overflow: hidden; }
.img-container img { max-width: 100%; display: block; }
#cropModalProfil { z-index: 1070 !important; }
.modal-dialog-scrollable .modal-body  { max-height: 65vh; overflow-y: auto; }
.modal-dialog-scrollable .modal-content {
    max-height: 90vh; display: flex; flex-direction: column;
}
.modal-footer { flex-shrink: 0; }

@media(max-width:768px) { .hero-banner { padding: 25px; } .hero-banner h2 { font-size: 24px; } }
</style>
</head>
<body>

<!-- ══════════ NAVBAR ══════════ -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">

        <button class="btn btn-light me-2"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarMenu">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <div class="brand-logo"><i class="bi bi-mortarboard-fill"></i></div>
            <span class="brand-text">Sistem Konsultasi</span>
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">
            <?php if ($role == 'dosen'): ?>
            <img src="<?= $foto_profil_src ?>"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #e0e7ff;"
                 alt="Foto"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($nama_user) ?>&background=10b981&color=fff&size=200'">
            <?php endif; ?>
            <span class="d-none d-md-block">Halo, <strong><?= htmlspecialchars($nama_user) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm btn-logout">Logout</a>
        </div>
    </div>
</nav>

<!-- ══════════ SIDEBAR OFFCANVAS ══════════ -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" style="width:300px;">
    <div class="offcanvas-header border-bottom pb-3">
        <h5 class="fw-bold mb-0">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body pt-3">

        <!-- User Card di Sidebar -->
        <div class="sidebar-user-card">
            <?php if ($role == 'dosen'): ?>
            <img src="<?= $foto_profil_src ?>"
                 class="sidebar-foto mb-2"
                 alt="Foto Profil"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($nama_user) ?>&background=10b981&color=fff&size=200'">
            <?php else: ?>
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;margin:0 auto 8px;color:white;font-size:28px;font-weight:700;">
                <?= mb_strtoupper(mb_substr($nama_user, 0, 1)) ?>
            </div>
            <?php endif; ?>
            <h6 class="fw-bold mb-1"><?= htmlspecialchars($nama_user) ?></h6>
            <span class="sidebar-role-badge"><?= ucfirst($role) ?></span>
            <?php if ($role == 'dosen' && $dosen_data): ?>
            <p class="text-muted small mt-1 mb-0"><?= htmlspecialchars($dosen_data['nip']) ?></p>
            <?php endif; ?>
        </div>

        <div class="mb-2"></div>

        <a href="dashboard.php" class="menu-link active">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <?php if ($role == 'admin'): ?>
        <a href="dosen.php" class="menu-link">
            <i class="bi bi-people-fill"></i> Data Dosen
        </a>
        <a href="mahasiswa.php" class="menu-link">
            <i class="bi bi-mortarboard-fill"></i> Data Mahasiswa
        </a>
        <a href="jadwal_admin.php" class="menu-link">
            <i class="bi bi-calendar-check-fill"></i> Semua Jadwal
        </a>
        <?php endif; ?>

        <?php if ($role == 'dosen'): ?>
        <a href="jadwal_dosen.php" class="menu-link">
            <i class="bi bi-calendar-week-fill"></i> Jadwal Saya
        </a>
        <a href="#" class="menu-link" data-bs-dismiss="offcanvas"
           onclick="setTimeout(()=>new bootstrap.Modal(document.getElementById('profilModal')).show(),300)">
            <i class="bi bi-person-circle"></i> Profil Saya
        </a>
        <?php endif; ?>

        <?php if ($role == 'mahasiswa'): ?>
        <a href="pengajuan.php" class="menu-link">
            <i class="bi bi-file-earmark-plus-fill"></i> Buat Pengajuan
        </a>
        <a href="jadwal_saya.php" class="menu-link">
            <i class="bi bi-clock-history"></i> Riwayat Saya
        </a>
        <?php endif; ?>

    </div>
</div>

<!-- ══════════ CONTENT ══════════ -->
<div class="container py-4">

    <div class="d-flex justify-content-end mb-3">
        <div class="clock-badge" id="clock"></div>
    </div>

    <!-- HERO -->
    <div class="hero-banner mb-4">
        <div class="d-flex align-items-center gap-4">
            <?php if ($role == 'dosen'): ?>
            <img src="<?= $foto_profil_src ?>"
                 class="hero-foto d-none d-md-block"
                 alt="Foto"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($nama_user) ?>&background=ffffff&color=10b981&size=200'">
            <?php endif; ?>
            <div>
                <h2>Selamat Datang, <?= htmlspecialchars($nama_user) ?> 👋</h2>
                <p class="mb-0 opacity-75">
                    Anda login sebagai <strong><?= ucfirst($role) ?></strong>.
                    Kelola konsultasi akademik dengan mudah dan cepat.
                </p>
                <?php if ($role == 'dosen' && $dosen_data): ?>
                <small class="opacity-75">
                    <i class="bi bi-award-fill me-1"></i>
                    <?= htmlspecialchars($dosen_data['kategori_keahlian']) ?>
                </small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- STATS ADMIN -->
    <?php if ($role == 'admin'): ?>
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-purple me-3"><i class="bi bi-people-fill"></i></div>
                    <div><small class="text-muted">Total Dosen</small><h3 class="mb-0"><?= $total_dosen ?></h3></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-green me-3"><i class="bi bi-mortarboard-fill"></i></div>
                    <div><small class="text-muted">Mahasiswa</small><h3 class="mb-0"><?= $total_mahasiswa ?></h3></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-blue me-3"><i class="bi bi-calendar-check-fill"></i></div>
                    <div><small class="text-muted">Total Jadwal</small><h3 class="mb-0"><?= $total_jadwal ?></h3></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-orange me-3"><i class="bi bi-hourglass-split"></i></div>
                    <div><small class="text-muted">Menunggu</small><h3 class="mb-0"><?= $total_menunggu ?></h3></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DASHBOARD CARDS -->
    <div class="row g-4">

        <?php if ($role == 'admin'): ?>
        <div class="col-lg-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">
                    <div class="card-icon icon-primary mb-3"><i class="bi bi-people-fill"></i></div>
                    <h4>Data Dosen</h4>
                    <p class="text-muted">Kelola seluruh data dosen.</p>
                    <a href="dosen.php" class="btn btn-primary w-100">Kelola Dosen</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">
                    <div class="card-icon icon-success mb-3"><i class="bi bi-mortarboard-fill"></i></div>
                    <h4>Data Mahasiswa</h4>
                    <p class="text-muted">Kelola data mahasiswa.</p>
                    <a href="mahasiswa.php" class="btn btn-success w-100">Kelola Mahasiswa</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">
                    <div class="card-icon icon-dark mb-3"><i class="bi bi-calendar-check"></i></div>
                    <h4>Semua Jadwal</h4>
                    <p class="text-muted">Pantau seluruh pengajuan.</p>
                    <a href="jadwal_admin.php" class="btn btn-dark w-100">Lihat Jadwal</a>
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
                    <div class="card-icon icon-primary mb-3"><i class="bi bi-file-earmark-plus-fill"></i></div>
                    <h4>Buat Pengajuan</h4>
                    <a href="pengajuan.php" class="btn btn-primary w-100">Buat Pengajuan</a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center p-4">
                    <div class="card-icon icon-info mb-3"><i class="bi bi-clock-history"></i></div>
                    <h4>Riwayat Saya</h4>
                    <a href="jadwal_saya.php" class="btn btn-info text-white w-100">Lihat Jadwal</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ══════════ MODAL EDIT PROFIL (dosen) ══════════ -->
<?php if ($role == 'dosen' && $dosen_data): ?>
<div class="modal fade" id="profilModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 overflow-hidden">

            <div class="modal-header text-white border-0"
                 style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-gear me-2"></i>Edit Profil Saya
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="formProfil">
                <input type="hidden" name="update_profil" value="1">
                <input type="hidden" name="cropped_foto_profil" id="croppedFotoProfil">

                <div class="modal-body px-4">

                    <!-- Foto + ganti foto -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="<?= $foto_profil_src ?>"
                                 id="previewFotoProfil"
                                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid #fef3c7;box-shadow:0 4px 15px rgba(245,158,11,.25);"
                                 alt="Foto Profil"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($nama_user) ?>&background=10b981&color=fff&size=200'">
                            <label for="inputFotoProfil"
                                   style="position:absolute;bottom:0;right:0;width:32px;height:32px;background:linear-gradient(135deg,#f59e0b,#ef4444);border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid white;cursor:pointer;"
                                   title="Ganti Foto">
                                <i class="bi bi-camera-fill" style="font-size:12px;color:white;"></i>
                            </label>
                        </div>
                        <input type="file" id="inputFotoProfil" class="d-none"
                               accept="image/png,image/jpeg,image/jpg">
                        <div id="statusFotoProfil" class="mt-2">
                            <small class="text-muted">Klik kamera untuk ganti foto profil</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-semibold small text-muted text-uppercase mb-1">NIP / NIDN</label>
                        <input type="text" name="nip" class="form-control form-control-profil"
                               value="<?= htmlspecialchars($dosen_data['nip']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted text-uppercase mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" name="nama_dosen" class="form-control form-control-profil"
                               value="<?= htmlspecialchars($dosen_data['nama_dosen']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted text-uppercase mb-1">Kategori Keahlian</label>
                        <input type="text" name="kategori_keahlian" class="form-control form-control-profil"
                               value="<?= htmlspecialchars($dosen_data['kategori_keahlian']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-semibold small text-muted text-uppercase mb-1">Password Baru</label>
                        <input type="password" name="password" class="form-control form-control-profil"
                               placeholder="Kosongkan jika tidak ingin mengganti...">
                    </div>

                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white fw-semibold px-4"
                            style="background:linear-gradient(135deg,#f59e0b,#ef4444);border:none;border-radius:12px;">
                        <i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- CROP MODAL untuk profil -->
<div class="modal fade" id="cropModalProfil" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white border-0"
                 style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-crop me-2"></i>Sesuaikan Foto Profil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container bg-dark">
                    <img id="imageToCropProfil" src="" alt="Crop">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn text-white fw-bold" id="btnCropProfil"
                        style="background:linear-gradient(135deg,#f59e0b,#ef4444);border:none;border-radius:10px;">
                    <i class="bi bi-scissors me-1"></i>Crop & Gunakan
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══════════ SCRIPTS ══════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
/* ── JAM ───────────────────────── */
function updateClock() {
    const now = new Date();
    document.getElementById("clock").innerHTML =
        now.toLocaleDateString("id-ID",{weekday:"long",day:"numeric",month:"long",year:"numeric"})
        + " | " + now.toLocaleTimeString("id-ID");
}
setInterval(updateClock,1000);
updateClock();

/* ── LOGOUT KONFIRMASI ─────────── */
document.querySelectorAll('.btn-logout').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const url = this.getAttribute('href');
        Swal.fire({
            title:'Keluar dari sistem?',
            text:'Sesi login akan diakhiri.',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Logout',
            cancelButtonText:'Batal'
        }).then(r => { if(r.isConfirmed) window.location.href = url; });
    });
});

/* ── LOADING SAAT SUBMIT ────────── */
document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', () => {
        Swal.fire({
            title:'Menyimpan Perubahan',
            text:'Mohon tunggu...',
            allowOutsideClick:false, allowEscapeKey:false, showConfirmButton:false,
            didOpen: () => Swal.showLoading()
        });
    });
});

/* ── CROPPER.JS — PROFIL ────────── */
<?php if ($role == 'dosen' && $dosen_data): ?>
let cropperProfil = null;
const inputFotoProfil    = document.getElementById('inputFotoProfil');
const imageToCropProfil  = document.getElementById('imageToCropProfil');
const cropModalProfilEl  = document.getElementById('cropModalProfil');
const cropModalProfil    = new bootstrap.Modal(cropModalProfilEl);
const croppedFotoProfil  = document.getElementById('croppedFotoProfil');
const statusFotoProfil   = document.getElementById('statusFotoProfil');
const previewFotoProfil  = document.getElementById('previewFotoProfil');

inputFotoProfil.addEventListener('change', function(e){
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        imageToCropProfil.src = ev.target.result;
        // Tutup profil modal dulu, baru buka crop
        bootstrap.Modal.getInstance(document.getElementById('profilModal')).hide();
        setTimeout(() => cropModalProfil.show(), 350);
    };
    reader.readAsDataURL(file);
});

cropModalProfilEl.addEventListener('shown.bs.modal', () => {
    cropperProfil = new Cropper(imageToCropProfil, {
        aspectRatio:1, viewMode:1, dragMode:'move', autoCropArea:1,
        restore:false, guides:true, center:true, highlight:false,
        cropBoxMovable:true, cropBoxResizable:true, toggleDragModeOnDblclick:false
    });
});

cropModalProfilEl.addEventListener('hidden.bs.modal', () => {
    if (cropperProfil) { cropperProfil.destroy(); cropperProfil = null; }
    // Kembalikan profil modal
    setTimeout(() => new bootstrap.Modal(document.getElementById('profilModal')).show(), 200);
});

document.getElementById('btnCropProfil').addEventListener('click', () => {
    if (!cropperProfil) return;
    const canvas  = cropperProfil.getCroppedCanvas({ width:400, height:400 });
    const dataUrl = canvas.toDataURL('image/png');
    croppedFotoProfil.value    = dataUrl;
    previewFotoProfil.src      = dataUrl;
    statusFotoProfil.innerHTML = "<small class='text-success fw-bold'><i class='bi bi-check-circle-fill me-1'></i>Foto baru siap disimpan!</small>";
    cropModalProfil.hide();
});
<?php endif; ?>
</script>

<!-- NOTIFIKASI -->
<?php if ($pesan_sukses_profil != ""): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        icon:'success', title:'Profil Diperbarui!',
        text:'<?= addslashes($pesan_sukses_profil) ?>',
        timer:2500, showConfirmButton:false
    });
});
</script>
<?php endif; ?>

<?php if ($pesan_error_profil != ""): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({ icon:'error', title:'Gagal!', text:'<?= addslashes($pesan_error_profil) ?>' });
});
</script>
<?php endif; ?>

<?php if (isset($_SESSION['login_success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
        toast:true, position:'top-end', icon:'success',
        title:'Login berhasil',
        text:'Selamat datang <?= htmlspecialchars(addslashes($_SESSION['nama_mhs'])) ?>',
        showConfirmButton:false, timer:3000, timerProgressBar:true
    });
});
</script>
<?php unset($_SESSION['login_success']); endif; ?>

</body>
</html>
