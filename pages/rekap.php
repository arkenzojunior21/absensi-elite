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
$allowed_users = ['admin', 'guru', 'piket_admin'];
// ─────────────────────────────────────────────────────────────────────────────

$uid = $_SESSION['id'];
$q_me = mysqli_query($conn, "SELECT username FROM users WHERE id='$uid'");
$me   = mysqli_fetch_assoc($q_me);
$my_username = $me['username'] ?? '';

if(!in_array($my_username, $allowed_users)){
    // Tampilan Akses Ditolak Bersih & Humanized
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Akses Dibatasi — ABSENSI ELITE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css?v=<?= time() ?>">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card" style="text-align: center;">
    <div style="width: 52px; height: 52px; border-radius: var(--radius); background: var(--danger-light); border: 1px solid var(--danger-border); color: var(--danger); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
      <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
    </div>
    <h1 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text);">Akses Dibatasi</h1>
    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.5rem;">
      Akun Anda tidak memiliki izin untuk mengakses halaman rekap absensi. Fitur ini hanya dapat diakses oleh administrator atau guru piket.
    </p>
    <a href="dashboard.php" class="btn btn-primary btn-block">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m15 18-6-6 6-6"/>
      </svg>
      <span>Kembali ke Dashboard</span>
    </a>
  </div>
</div>
</body>
</html>
    <?php
    exit;
}

// ── FILTER BULAN & TAHUN ──────────────────────────────────────────────────────
$bulan_list = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$bulan_sel = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun_sel = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

if($bulan_sel < 1 || $bulan_sel > 12) $bulan_sel = (int)date('m');
if($tahun_sel < 2020 || $tahun_sel > 2099) $tahun_sel = (int)date('Y');

// ── DATA ABSENSI BULAN INI ────────────────────────────────────────────────────
$q_absen = mysqli_query($conn,
    "SELECT a.*, u.nama, u.kelas, u.jurusan
     FROM absensi a
     JOIN users u ON u.id = a.user_id
     WHERE MONTH(a.tanggal)='$bulan_sel' AND YEAR(a.tanggal)='$tahun_sel'
     ORDER BY a.tanggal ASC, u.nama ASC"
);

// ── RINGKASAN PER SISWA ───────────────────────────────────────────────────────
$q_summary = mysqli_query($conn,
    "SELECT u.nama, u.kelas, u.jurusan,
            COUNT(a.id) as total,
            SUM(a.status='Hadir') as hadir,
            SUM(a.status!='Hadir') as lainnya
     FROM absensi a
     JOIN users u ON u.id = a.user_id
     WHERE MONTH(a.tanggal)='$bulan_sel' AND YEAR(a.tanggal)='$tahun_sel'
     GROUP BY u.id
     ORDER BY u.nama ASC"
);

$total_absen = mysqli_num_rows($q_absen);
$q_count_users = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
$total_siswa = mysqli_fetch_assoc($q_count_users)['c'] ?? 30;

// Hitung total hadir global
$q_hadir_total = mysqli_query($conn,
    "SELECT COUNT(*) as c FROM absensi
     WHERE MONTH(tanggal)='$bulan_sel' AND YEAR(tanggal)='$tahun_sel' AND status='Hadir'"
);
$total_hadir = mysqli_fetch_assoc($q_hadir_total)['c'] ?? 0;
$total_tidak_hadir = max(0, $total_absen - $total_hadir);

// ── TAHUN YANG TERSEDIA ───────────────────────────────────────────────────────
$q_years = mysqli_query($conn, "SELECT DISTINCT YEAR(tanggal) as y FROM absensi ORDER BY y DESC");
$years_available = [];
while($yr = mysqli_fetch_assoc($q_years)) {
    if(!empty($yr['y'])) $years_available[] = (int)$yr['y'];
}
if(!in_array((int)date('Y'), $years_available)) $years_available[] = (int)date('Y');
rsort($years_available);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Absensi Bulanan — ABSENSI ELITE</title>
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
<main class="app-content">
  <div class="page-header">
    <div class="page-header-row">
      <div>
        <h1 style="font-size: clamp(1.45rem, 3vw, 1.85rem); font-weight: 800; margin-bottom: 0.35rem;">
          Rekap Absensi Bulanan
        </h1>
        <p style="font-size: 0.92rem; color: var(--text-muted);">
          Laporan kehadiran siswa periode <strong><?= $bulan_list[$bulan_sel] ?> <?= $tahun_sel ?></strong>
        </p>
      </div>

      <!-- Tombol Ekspor PDF -->
      <a href="rekap_pdf.php?bulan=<?= $bulan_sel ?>&tahun=<?= $tahun_sel ?>" target="_blank" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" x2="12" y1="15" y2="3"/>
        </svg>
        <span>Cetak / Ekspor PDF</span>
      </a>
    </div>
  </div>

  <!-- Filter Bar -->
  <form method="GET" action="" class="filter-bar-card">
    <span class="filter-bar-label">Filter Periode:</span>
    <select name="bulan" class="filter-select">
      <?php foreach($bulan_list as $num => $nama): ?>
      <option value="<?= $num ?>" <?= ($bulan_sel == $num) ? 'selected' : '' ?>><?= $nama ?></option>
      <?php endforeach; ?>
    </select>

    <select name="tahun" class="filter-select">
      <?php foreach($years_available as $y): ?>
      <option value="<?= $y ?>" <?= ($tahun_sel == $y) ? 'selected' : '' ?>><?= $y ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.15rem;">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" x2="16.65" y1="21" y2="16.65"/>
      </svg>
      <span>Tampilkan Data</span>
    </button>
  </form>

  <!-- Summary Stats 4 Kolom -->
  <div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card" style="background: #fff8ee; border: 1.5px solid #fed7aa;">
      <div class="stat-value color-dark"><?= $total_siswa ?></div>
      <div class="stat-label" style="color: #9a3412;">Total Siswa Terdaftar</div>
    </div>
    <div class="stat-card" style="background: #fef3c7; border: 1.5px solid #fde68a;">
      <div class="stat-value color-yellow"><?= $total_absen ?></div>
      <div class="stat-label" style="color: #b45309;">Total Catatan Absensi</div>
    </div>
    <div class="stat-card" style="background: #ffedd5; border: 1.5px solid #fdba74;">
      <div class="stat-value color-orange"><?= $total_hadir ?></div>
      <div class="stat-label" style="color: #c2410c;">Kehadiran (Hadir)</div>
    </div>
    <div class="stat-card" style="background: #fff4ea; border: 1.5px solid #fecaca;">
      <div class="stat-value color-dark"><?= $total_tidak_hadir ?></div>
      <div class="stat-label" style="color: #991b1b;">Status Non-Hadir</div>
    </div>
  </div>

  <!-- Tabel 1: Ringkasan per Siswa -->
  <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1.15rem 1.25rem; margin-bottom: 0; border-bottom: 1px solid var(--border);">
      <div class="card-title">
        <div class="card-icon-badge card-icon-yellow">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <span>Ringkasan Kehadiran per Siswa</span>
      </div>
    </div>

    <div class="table-container" style="border: none; border-radius: 0; box-shadow: none;">
      <table class="app-table">
        <thead>
          <tr>
            <th style="width: 45px;">#</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Jurusan</th>
            <th style="text-align: center;">Total</th>
            <th style="text-align: center;">Hadir</th>
            <th style="text-align: center;">Tidak Hadir</th>
            <th style="text-align: center;">Persentase</th>
          </tr>
        </thead>
        <tbody>
          <?php if(mysqli_num_rows($q_summary) == 0): ?>
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <div class="empty-state-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" x2="12" y1="8" y2="12"/>
                    <line x1="12" x2="12.01" y1="16" y2="16"/>
                  </svg>
                </div>
                <div class="empty-state-title">Tidak Ada Data Siswa</div>
                <p class="empty-state-desc">Belum ada catatan absensi siswa pada periode <?= $bulan_list[$bulan_sel] ?> <?= $tahun_sel ?>.</p>
              </div>
            </td>
          </tr>
          <?php else: 
            $no = 1;
            mysqli_data_seek($q_summary, 0);
            while($row = mysqli_fetch_assoc($q_summary)):
              $total_row = (int)$row['total'];
              $hadir_row = (int)$row['hadir'];
              $lain_row  = (int)$row['lainnya'];
              $pct = ($total_row > 0) ? round(($hadir_row / $total_row) * 100) : 0;
          ?>
          <tr>
            <td style="color: var(--text-muted); font-size: 0.8rem;"><?= $no++ ?></td>
            <td style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['kelas']) ?></td>
            <td><?= htmlspecialchars($row['jurusan']) ?></td>
            <td style="text-align: center; font-weight: 600;"><?= $total_row ?></td>
            <td style="text-align: center;">
              <span class="badge badge-hadir"><?= $hadir_row ?></span>
            </td>
            <td style="text-align: center;">
              <?php if($lain_row > 0): ?>
                <span class="badge badge-lain"><?= $lain_row ?></span>
              <?php else: ?>
                <span style="color: var(--text-subtle);">0</span>
              <?php endif; ?>
            </td>
            <td style="text-align: center; font-weight: 700; font-variant-numeric: tabular-nums; color: <?= ($pct >= 75) ? 'var(--success)' : (($pct >= 50) ? 'var(--warning)' : 'var(--danger)') ?>;">
              <?= $pct ?>%
            </td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Tabel 2: Detail Seluruh Log Absensi -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="card-header" style="padding: 1.15rem 1.25rem; margin-bottom: 0; border-bottom: 1px solid var(--border);">
      <div class="card-title">
        <div class="card-icon-badge card-icon-orange">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="8" x2="21" y1="6" y2="6"/>
            <line x1="8" x2="21" y1="12" y2="12"/>
            <line x1="8" x2="21" y1="18" y2="18"/>
            <line x1="3" x2="3.01" y1="6" y2="6"/>
            <line x1="3" x2="3.01" y1="12" y2="12"/>
            <line x1="3" x2="3.01" y1="18" y2="18"/>
          </svg>
        </div>
        <span>Detail Riwayat Semua Absensi</span>
      </div>
      <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">
        Total <?= $total_absen ?> baris data
      </span>
    </div>

    <div class="table-container" style="border: none; border-radius: 0; box-shadow: none;">
      <table class="app-table">
        <thead>
          <tr>
            <th style="width: 45px;">#</th>
            <th>Waktu Absen</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Jurusan</th>
            <th>Status Kehadiran</th>
            <th>Jarak GPS</th>
          </tr>
        </thead>
        <tbody>
          <?php if($total_absen == 0): ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <div class="empty-state-title">Belum Ada Riwayat</div>
                <p class="empty-state-desc">Tidak ditemukan transaksi absensi pada periode ini.</p>
              </div>
            </td>
          </tr>
          <?php else: 
            $no2 = 1;
            mysqli_data_seek($q_absen, 0);
            while($row = mysqli_fetch_assoc($q_absen)):
              $waktu = (!empty($row['tanggal'])) ? date('d M Y, H:i', strtotime($row['tanggal'])) : '—';
              $isHadir = (strtolower($row['status']) === 'hadir');
          ?>
          <tr>
            <td style="color: var(--text-muted); font-size: 0.8rem;"><?= $no2++ ?></td>
            <td style="font-variant-numeric: tabular-nums; white-space: nowrap; font-size: 0.84rem; color: var(--text-muted);">
              <?= $waktu ?>
            </td>
            <td style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['kelas']) ?></td>
            <td><?= htmlspecialchars($row['jurusan']) ?></td>
            <td>
              <span class="badge <?= $isHadir ? 'badge-hadir' : 'badge-lain' ?>">
                <?= htmlspecialchars($row['status']) ?>
              </span>
            </td>
            <td style="font-variant-numeric: tabular-nums; color: var(--text-muted);">
              <?= htmlspecialchars($row['jarak']) ?> m
            </td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

</body>
</html>