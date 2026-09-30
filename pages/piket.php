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

if(isset($_POST['piket'])){
    $user_id   = $_SESSION['id'];
    $kegiatan  = $_POST['kegiatan'];
    $latitude  = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $jarak     = $_POST['jarak'];

    if($jarak > 500){
        echo "<script>alert('Piket ditolak: lokasi Anda di luar radius sekolah.');window.location.href='piket.php';</script>";
        exit;
    }

    $status = "Piket Selesai";
    $foto   = $_FILES['foto']['name'];
    $tmp    = $_FILES['foto']['tmp_name'];
    move_uploaded_file($tmp, "../uploads/".$foto);

    mysqli_query($conn,
        "INSERT INTO piket (user_id,kegiatan,foto,latitude,longitude,jarak,status)
         VALUES ('$user_id','$kegiatan','$foto','$latitude','$longitude','$jarak','$status')"
    );

    echo "<script>alert('Data piket berhasil dikirim');window.location.href='piket.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi Piket — ABSENSI ELITE</title>
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
      <a href="dashboard.php" class="btn-nav-back">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m15 18-6-6 6-6"/>
        </svg>
        <span>Kembali ke Dashboard</span>
      </a>
    </div>
  </div>
</nav>

<!-- Main Form Container -->
<main class="app-content-narrow">
  <div class="page-header">
    <h1 style="font-size: clamp(1.45rem, 3vw, 1.85rem); font-weight: 800; margin-bottom: 0.35rem;">
      Absensi Piket Harian
    </h1>
    <p style="font-size: 0.92rem; color: var(--text-muted);">
      Isi rincian kegiatan piket kebersihan dan lampirkan foto dokumentasi sebagai bukti.
    </p>
  </div>

  <form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="latitude" id="inputLatitude">
    <input type="hidden" name="longitude" id="inputLongitude">
    <input type="hidden" name="jarak" id="inputJarak">

    <!-- Card 1: Data Piket -->
    <div class="card" style="border: 1.5px solid #fed7aa; background: #fffbf7;">
      <div class="card-header">
        <div class="card-title">
          <div class="card-icon-badge card-icon-orange">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 20h9"/>
              <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
          </div>
          <span>Informasi Kegiatan Piket</span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="kegiatan">Deskripsi Pekerjaan Piket</label>
        <textarea id="kegiatan" name="kegiatan" placeholder="Tuliskan kegiatan yang dilakukan, contoh: Menyapu lantai kelas, merapikan meja guru, dan membersihkan papan tulis..." required></textarea>
        <p class="form-hint">Jelaskan kegiatan piket secara singkat dan jelas.</p>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label" for="fotoPiket">Foto Bukti Kegiatan</label>
        <input type="file" id="fotoPiket" name="foto" accept="image/*" capture="camera" class="file-upload-box" required>
        <p class="form-hint">Gunakan kamera perangkat untuk mengambil foto dokumentasi langsung di lokasi.</p>
      </div>
    </div>

    <!-- Card 2: GPS Verification -->
    <div class="card" style="border: 1.5px solid #fde68a; background: #fffcf4;">
      <div class="card-header">
        <div class="card-title">
          <div class="card-icon-badge card-icon-yellow">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <span>Verifikasi Lokasi Sekolah</span>
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
          <div class="gps-label">Batas Maksimal</div>
          <div class="gps-value">100 <small>meter</small></div>
        </div>

        <div class="gps-status-box idle" id="statusBar">
          <span class="status-indicator-dot idle" id="statusDot"></span>
          <span id="status">Lokasi belum diverifikasi</span>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid-2" style="gap: 0.85rem; margin-top: 1rem;">
      <button type="button" onclick="cekLokasi()" class="btn btn-gps-check btn-block">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
        <span>Verifikasi Lokasi GPS</span>
      </button>

      <button type="submit" name="piket" id="btnPiket" disabled class="btn btn-primary btn-block">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14"/>
          <path d="m12 5 7 7-7 7"/>
        </svg>
        <span>Kirim Laporan Piket</span>
      </button>
    </div>

  </form>
</main>

<script>
const sekolahLat = -7.257641452891945;
const sekolahLon = 112.72497377247612;
const maxRadius  = 100;

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
    document.getElementById('inputLatitude').value   = lat;
    document.getElementById('inputLongitude').value  = lon;
    document.getElementById('inputJarak').value      = jarak;

    const bar  = document.getElementById('statusBar');
    const dot  = document.getElementById('statusDot');
    const stat = document.getElementById('status');
    const btn  = document.getElementById('btnPiket');

    if (jarak <= maxRadius) {
      bar.className  = 'gps-status-box valid';
      dot.className  = 'status-indicator-dot valid';
      stat.textContent = 'Lokasi Valid — berada dalam area sekolah';
      btn.disabled   = false;
    } else {
      bar.className  = 'gps-status-box invalid';
      dot.className  = 'status-indicator-dot invalid';
      stat.textContent = 'Di luar area sekolah (' + jarak + ' m)';
      btn.disabled   = true;
    }
  }, () => {
    alert("GPS tidak aktif atau izin akses ditolak. Silakan aktifkan GPS perangkat Anda.");
  });
}
</script>

</body>
</html>