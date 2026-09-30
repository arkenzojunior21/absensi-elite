<?php
session_start();
$koneksi_path = file_exists(__DIR__ . '/../config/koneksi.php') 
    ? __DIR__ . '/../config/koneksi.php' 
    : $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';
include $koneksi_path;

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// ── USERNAME YANG BOLEH AKSES REKAP ──────────────────────────────────────────
$allowed_rekap = ['admin', 'guru', 'piket_admin'];
// ─────────────────────────────────────────────────────────────────────────────

$uid     = $_SESSION['id'];
$q_me    = mysqli_query($conn, "SELECT username FROM users WHERE id='$uid'");
$me      = mysqli_fetch_assoc($q_me);
$is_admin = in_array($me['username'] ?? '', $allowed_rekap);

if(isset($_POST['absen'])){
    $user_id   = $_SESSION['id'];
    $latitude  = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $jarak     = $_POST['jarak'];

    if($jarak > 500){
        echo "<script>alert('Absensi gagal: lokasi Anda berada di luar radius yang ditentukan.');window.location.href='dashboard.php';</script>";
        exit;
    }

    $status = "Hadir";
    $foto   = $_FILES['foto']['name'];
    $tmp    = $_FILES['foto']['tmp_name'];
    move_uploaded_file($tmp, "../uploads/".$foto);

    mysqli_query($conn,
        "INSERT INTO absensi (user_id,latitude,longitude,jarak,foto,status)
         VALUES ('$user_id','$latitude','$longitude','$jarak','$foto','$status')"
    );

    echo "<script>alert('Absensi berhasil disimpan');window.location.href='dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — ABSENSI ELITE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css?v=<?= time() ?>">
</head>
<body>

<!-- Navigation Bar -->
<nav class="app-navbar">
  <div class="nav-container">
    <div class="brand-wrapper">
      <div class="brand-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
          <line x1="16" x2="16" y1="2" y2="6"/>
          <line x1="8" x2="8" y1="2" y2="6"/>
          <line x1="3" x2="21" y1="10" y2="10"/>
          <path d="m9 16 2 2 4-4"/>
        </svg>
      </div>
      <div class="brand-title">ABSENSI <span>ELITE</span></div>
    </div>

    <div class="nav-actions">
      <?php if($is_admin): ?>
      <span class="admin-pill">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Admin
      </span>
      <?php endif; ?>

      <div class="user-chip">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['nama'] ?? 'Siswa') ?></span>
      </div>

      <a href="logout.php" class="btn-nav-logout" title="Keluar dari sesi">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" x2="9" y1="12" y2="12"/>
        </svg>
        <span>Keluar</span>
      </a>
    </div>
  </div>
</nav>

<!-- Main Container -->
<main class="app-content">
  <div class="page-header">
    <div class="page-header-row">
      <div>
        <h1 class="header-greeting">
          Halo, <span><?= htmlspecialchars(explode(' ', $_SESSION['nama'] ?? '')[0]) ?></span>
        </h1>
        <p style="font-size: 0.92rem; color: var(--text-muted);">
          Silakan verifikasi lokasi dan lengkapi bukti kehadiran hari ini.
        </p>
      </div>
      <div class="date-pill">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
        <span id="tanggal-hari">Memuat tanggal...</span>
      </div>
    </div>
  </div>

  <!-- 2-Column Grid: GPS Card & Absensi Form Card -->
  <div class="grid-2" style="margin-bottom: 1.5rem;">

    <!-- Card 1: Verifikasi Lokasi GPS -->
    <div class="card" style="margin-bottom: 0; border: 1.5px solid #fde68a; background: #fffcf4;">
      <div class="card-header">
        <div class="card-title">
          <div class="card-icon-badge card-icon-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <span>Verifikasi Lokasi GPS</span>
        </div>
      </div>

      <div class="gps-grid">
        <div class="gps-item">
          <div class="gps-label">Latitude</div>
          <div class="gps-value" id="latitude">—</div>
        </div>

        <div class="gps-item">
          <div class="gps-label">Longitude</div>
          <div class="gps-value" id="longitude">—</div>
        </div>

        <div class="gps-item">
          <div class="gps-label">Jarak ke Sekolah</div>
          <div class="gps-value"><span id="jarak">—</span> <small>meter</small></div>
        </div>

        <div class="gps-item">
          <div class="gps-label">Radius Maksimal</div>
          <div class="gps-value">100 <small>meter</small></div>
        </div>

        <div class="gps-status-box idle" id="statusBar">
          <span class="status-indicator-dot idle" id="statusDot"></span>
          <span id="status">Status lokasi belum dicek</span>
        </div>
      </div>

      <p class="form-hint" style="margin-top: 0.5rem;">
        Pastikan GPS perangkat Anda aktif dan browser telah diizinkan untuk mengakses lokasi.
      </p>
    </div>

    <!-- Card 2: Form Absensi Harian -->
    <div class="card" style="margin-bottom: 0; border: 1.5px solid #fed7aa; background: #fffbf7;">
      <div class="card-header">
        <div class="card-title">
          <div class="card-icon-badge card-icon-orange">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
          </div>
          <span>Formulir Absensi</span>
        </div>
      </div>

      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="latitude" id="inputLatitude">
        <input type="hidden" name="longitude" id="inputLongitude">
        <input type="hidden" name="jarak" id="inputJarak">

        <div class="form-group">
          <label class="form-label" for="fotoInput">Unggah Foto Selfie Kehadiran</label>
          <input type="file" id="fotoInput" name="foto" accept="image/*" capture="camera" class="file-upload-box" required>
          <p class="form-hint">Ambil foto selfie langsung dengan kamera perangkat seba.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 1.25rem;">
          <button type="button" onclick="cekLokasi()" class="btn btn-gps-check btn-block">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20"/>
              <path d="M2 12h20"/>
              <circle cx="12" cy="12" r="7"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <span>Cek Lokasi GPS</span>
          </button>

          <button type="submit" name="absen" id="btnAbsen" disabled class="btn btn-primary btn-block">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>
            <span>Kirim Absensi Sekarang</span>
          </button>
        </div>
      </form>
    </div>

  </div><!-- /grid-2 -->

  <!-- Quick Navigation Section -->
  <div style="margin-top: 1.5rem;">
    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.85rem;">
      Menu Cepat
    </h3>

    <div class="quick-nav-grid <?= $is_admin ? 'has-admin' : '' ?>">
      <!-- Piket -->
      <a href="piket.php" class="quick-nav-item item-orange">
        <div class="icon-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            <path d="M9 14h6"/>
            <path d="M9 18h6"/>
            <path d="M9 10h1"/>
          </svg>
        </div>
        <div class="item-content">
          <div class="item-tag">Kegiatan</div>
          <div class="item-title">Absensi Piket</div>
        </div>
        <svg class="item-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m9 18 6-6-6-6"/>
        </svg>
      </a>

      <!-- Riwayat -->
      <a href="riwayat.php" class="quick-nav-item item-yellow">
        <div class="icon-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div class="item-content">
          <div class="item-tag">Rekap Data</div>
          <div class="item-title">Riwayat Absensi</div>
        </div>
        <svg class="item-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m9 18 6-6-6-6"/>
        </svg>
      </a>

      <?php if($is_admin): ?>
      <!-- Rekap Admin -->
      <a href="rekap.php" class="quick-nav-item item-neutral">
        <div class="icon-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" x2="18" y1="20" y2="10"/>
            <line x1="12" x2="12" y1="20" y2="4"/>
            <line x1="6" x2="6" y1="20" y2="14"/>
          </svg>
        </div>
        <div class="item-content">
          <div class="item-tag">Administrator</div>
          <div class="item-title">Rekap Bulanan</div>
        </div>
        <svg class="item-arrow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m9 18 6-6-6-6"/>
        </svg>
      </a>
      <?php endif; ?>
    </div>
  </div>

</main>

<script>
// Tanggal Hari Ini
const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const now = new Date();
const dateElem = document.getElementById('tanggal-hari');
if(dateElem){
  dateElem.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
}

// Koordinat Target & Radius
const sekolahLat = -7.257641452891945;
const sekolahLon = 112.72497377247612;
const maxRadius  = 8000;

function hitungJarak(lat1, lon1, lat2, lon2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function cekLokasi() {
  if (!navigator.geolocation) {
    alert("Perangkat Anda tidak mendukung fitur geolokasi GPS.");
    return;
  }

  navigator.geolocation.getCurrentPosition((pos) => {
    const lat = pos.coords.latitude;
    const lon = pos.coords.longitude;
    const jarak = Math.round(hitungJarak(lat, lon, sekolahLat, sekolahLon));

    document.getElementById('latitude').textContent  = lat.toFixed(6);
    document.getElementById('longitude').textContent = lon.toFixed(6);
    document.getElementById('jarak').textContent     = jarak;

    document.getElementById('inputLatitude').value  = lat;
    document.getElementById('inputLongitude').value = lon;
    document.getElementById('inputJarak').value     = jarak;

    const bar  = document.getElementById('statusBar');
    const dot  = document.getElementById('statusDot');
    const stat = document.getElementById('status');
    const btn  = document.getElementById('btnAbsen');

    if (jarak <= maxRadius) {
      bar.className  = 'gps-status-box valid';
      dot.className  = 'status-indicator-dot valid';
      stat.textContent = 'Lokasi Valid — dalam area jangkauan sekolah';
      btn.disabled   = false;
    } else {
      bar.className  = 'gps-status-box invalid';
      dot.className  = 'status-indicator-dot invalid';
      stat.textContent = 'Di luar area jangkauan sekolah (' + jarak + ' m)';
      btn.disabled   = true;
    }
  }, (err) => {
    alert("GPS tidak aktif atau izin akses lokasi ditolak. Silakan aktifkan GPS perangkat.");
  });
}
</script>

</body>
</html>