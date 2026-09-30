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

$user_id = $_SESSION['id'];
$data = mysqli_query($conn,
    "SELECT * FROM absensi WHERE user_id='$user_id' ORDER BY id DESC"
);
$total = mysqli_num_rows($data);

// Hitung status hadir
$q_hadir = mysqli_query($conn, "SELECT COUNT(*) as c FROM absensi WHERE user_id='$user_id' AND status='Hadir'");
$hadir = mysqli_fetch_assoc($q_hadir)['c'] ?? 0;
$lainnya = max(0, $total - $hadir);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Absensi — ABSENSI ELITE</title>
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

<!-- Main Container -->
<main class="app-content-narrow">
  <div class="page-header">
    <h1 style="font-size: clamp(1.45rem, 3vw, 1.85rem); font-weight: 800; margin-bottom: 0.35rem;">
      Riwayat Kehadiran
    </h1>
    <p style="font-size: 0.92rem; color: var(--text-muted);">
      Catatan lengkap seluruh absensi atas nama <?= htmlspecialchars($_SESSION['nama']) ?>
    </p>
  </div>

  <!-- Summary Stats 3 Kolom -->
  <div class="grid-3" style="margin-bottom: 1.5rem;">
    <div class="stat-card" style="background: #fff8ee; border: 1.5px solid #fed7aa;">
      <div class="stat-value color-dark"><?= $total ?></div>
      <div class="stat-label" style="color: #9a3412;">Total Absensi</div>
    </div>
    <div class="stat-card" style="background: #ffedd5; border: 1.5px solid #fdba74;">
      <div class="stat-value color-orange"><?= $hadir ?></div>
      <div class="stat-label" style="color: #c2410c;">Kehadiran Tervalidasi</div>
    </div>
    <div class="stat-card" style="background: #fef3c7; border: 1.5px solid #fde68a;">
      <div class="stat-value color-yellow"><?= $lainnya ?></div>
      <div class="stat-label" style="color: #b45309;">Status Lainnya</div>
    </div>
  </div>

  <!-- Timeline List -->
  <div class="timeline-list">
    <?php if($total == 0): ?>
    <div class="card" style="padding: 3rem 1.5rem; text-align: center;">
      <div class="empty-state-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
          <line x1="16" x2="16" y1="2" y2="6"/>
          <line x1="8" x2="8" y1="2" y2="6"/>
          <line x1="3" x2="21" y1="10" y2="10"/>
        </svg>
      </div>
      <div class="empty-state-title">Belum Ada Catatan Absensi</div>
      <p class="empty-state-desc">Anda belum melakukan absensi kehadiran pada sistem.</p>
    </div>
    <?php else: 
      mysqli_data_seek($data, 0);
      while($row = mysqli_fetch_array($data)):
        $tanggal = $row['tanggal'] ?? '-';
        $tgl_obj = ($tanggal !== '-' && !empty($tanggal)) ? date_create($tanggal) : null;
        $day     = $tgl_obj ? date_format($tgl_obj, 'd') : '--';
        $month   = $tgl_obj ? date_format($tgl_obj, 'M') : '--';
        $time    = $tgl_obj ? date_format($tgl_obj, 'H:i') : '--:--';
        $status  = $row['status'] ?? 'Hadir';
        $isHadir = (strtolower($status) === 'hadir');
    ?>
    <div class="timeline-card">
      <div class="timeline-header">
        <div class="timeline-left">
          <div class="timeline-date-block">
            <div class="timeline-day"><?= $day ?></div>
            <div class="timeline-month"><?= $month ?></div>
          </div>
          <div class="timeline-meta">
            <div class="timeline-time">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              <span><?= $time ?> WIB</span>
            </div>
            <div class="timeline-title">Absensi Harian Siswa</div>
          </div>
        </div>

        <span class="badge <?= $isHadir ? 'badge-hadir' : 'badge-lain' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <?php if($isHadir): ?>
              <polyline points="20 6 9 17 4 12"/>
            <?php else: ?>
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" x2="12" y1="8" y2="12"/>
            <?php endif; ?>
          </svg>
          <?= htmlspecialchars($status) ?>
        </span>
      </div>

      <div class="timeline-grid">
        <div class="timeline-info-item">
          <div class="timeline-info-label">Latitude</div>
          <div class="timeline-info-val"><?= htmlspecialchars($row['latitude']) ?></div>
        </div>
        <div class="timeline-info-item">
          <div class="timeline-info-label">Longitude</div>
          <div class="timeline-info-val"><?= htmlspecialchars($row['longitude']) ?></div>
        </div>
        <div class="timeline-info-item">
          <div class="timeline-info-label">Jarak ke Sekolah</div>
          <div class="timeline-info-val"><?= htmlspecialchars($row['jarak']) ?> m</div>
        </div>
      </div>

      <?php if(!empty($row['foto'])): ?>
      <div class="timeline-photo-wrap" style="padding-top: 0.85rem;">
        <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Bukti Foto Absensi" loading="lazy" onclick="openLb(this.src)" title="Klik untuk memperbesar">
      </div>
      <?php endif; ?>
    </div>
    <?php endwhile; endif; ?>
  </div>

</main>

<!-- Lightbox Modal -->
<div class="lightbox-overlay" id="lb" onclick="closeLb()">
  <div class="lightbox-container" onclick="event.stopPropagation()">
    <button class="lightbox-close-btn" onclick="closeLb()" aria-label="Tutup foto">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
    <img id="lb-img" src="" alt="Foto Absensi Ukuran Penuh">
  </div>
</div>

<script>
function openLb(src) {
  document.getElementById('lb-img').src = src;
  document.getElementById('lb').classList.add('active');
}
function closeLb() {
  document.getElementById('lb').classList.remove('active');
}
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeLb();
});
</script>

</body>
</html>