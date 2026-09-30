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

// ── USERNAME YANG BOLEH AKSES ─────────────────────────────────────────────────
$allowed_users = ['admin', 'guru', 'piket_admin'];
// ─────────────────────────────────────────────────────────────────────────────

$uid  = $_SESSION['id'];
$q_me = mysqli_query($conn, "SELECT username FROM users WHERE id='$uid'");
$me   = mysqli_fetch_assoc($q_me);
if(!in_array($me['username'] ?? '', $allowed_users)){
    die("Akses ditolak.");
}

$bulan_list = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$bulan_sel = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun_sel = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
if($bulan_sel < 1 || $bulan_sel > 12) $bulan_sel = (int)date('m');
if($tahun_sel < 2020 || $tahun_sel > 2099) $tahun_sel = (int)date('Y');

// ── DATA ──────────────────────────────────────────────────────────────────────
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

$q_detail = mysqli_query($conn,
    "SELECT a.tanggal, a.status, a.jarak, a.latitude, a.longitude,
            u.nama, u.kelas, u.jurusan
     FROM absensi a
     JOIN users u ON u.id = a.user_id
     WHERE MONTH(a.tanggal)='$bulan_sel' AND YEAR(a.tanggal)='$tahun_sel'
     ORDER BY a.tanggal ASC, u.nama ASC"
);

$total_absen   = mysqli_num_rows($q_detail);
$total_siswa   = mysqli_num_rows($q_summary);
$q_h = mysqli_query($conn,
    "SELECT COUNT(*) as c FROM absensi
     WHERE MONTH(tanggal)='$bulan_sel' AND YEAR(tanggal)='$tahun_sel' AND status='Hadir'"
);
$total_hadir = mysqli_fetch_assoc($q_h)['c'] ?? 0;
$total_tidak_hadir = max(0, $total_absen - $total_hadir);

$periode = $bulan_list[$bulan_sel] . ' ' . $tahun_sel;
$generated = date('d M Y, H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Absensi <?= $periode ?> — ABSENSI ELITE</title>
<style>
/* ── RESET & BASE ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  font-size: 10pt;
  color: #1c1917;
  background: #fff;
}

/* ── PRINT BAR (Hanya Tampil di Layar) ── */
@media screen {
  .print-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 999;
    background: #ffffff;
    border-bottom: 1px solid #e7e5e4;
    padding: 0.75rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  }
  .print-bar-title {
    font-weight: 700;
    color: #1c1917;
    font-size: 0.95rem;
  }
  .print-bar-sub {
    color: #78716c;
    font-size: 0.8rem;
  }
  .btn-print {
    background: #ea580c;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.85rem;
    border: none;
    border-radius: 6px;
    padding: 0.55rem 1.25rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s ease;
  }
  .btn-print:hover {
    background: #c2410c;
  }
  .btn-back-pdf {
    background: #f5f5f4;
    border: 1px solid #d6d3d1;
    color: #44403c;
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    text-decoration: none;
  }
  .btn-back-pdf:hover {
    background: #e7e5e4;
  }
  .page {
    margin-top: 65px;
    padding: 1.5cm 2cm;
    max-width: 960px;
    margin-left: auto;
    margin-right: auto;
  }
}

@media print {
  .print-bar { display: none !important; }
  .page { padding: 0.8cm 1cm; width: 100%; }
  body { background: #fff; }
  @page { size: A4 portrait; margin: 1.2cm 1cm; }
}

/* ── HEADER DOKUMEN ── */
.doc-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  border-bottom: 2px solid #ea580c;
  padding-bottom: 12px;
  margin-bottom: 18px;
  gap: 1rem;
}
.doc-logo {
  font-size: 16pt;
  font-weight: 800;
  letter-spacing: -0.5px;
  color: #1c1917;
  line-height: 1.1;
}
.doc-logo span {
  color: #ea580c;
}
.doc-subtitle {
  font-size: 8.5pt;
  color: #78716c;
  margin-top: 3px;
}
.doc-meta {
  text-align: right;
  font-size: 8pt;
  color: #57534e;
  line-height: 1.6;
}
.doc-meta strong {
  color: #1c1917;
  font-size: 9pt;
}

/* ── PERIODE ── */
.doc-period {
  background: #fff7ed;
  border-left: 4px solid #ea580c;
  border-radius: 0 6px 6px 0;
  padding: 8px 12px;
  margin-bottom: 18px;
}
.doc-period-label {
  font-size: 7.5pt;
  color: #9a3412;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.05em;
  margin-bottom: 2px;
}
.doc-period-val {
  font-size: 13pt;
  font-weight: 800;
  color: #1c1917;
}

/* ── STATISTIK ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 20px;
}
.stat-box {
  border: 1px solid #e7e5e4;
  border-radius: 6px;
  padding: 8px 10px;
  background: #fafaf9;
}
.stat-box-num {
  font-size: 18pt;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 2px;
  color: #1c1917;
}
.stat-box.orange .stat-box-num { color: #ea580c; }
.stat-box.yellow .stat-box-num { color: #d97706; }
.stat-box-label {
  font-size: 7.5pt;
  color: #78716c;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.03em;
}

/* ── SECTION TITLE ── */
.section-title {
  font-size: 9.5pt;
  font-weight: 800;
  color: #1c1917;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.section-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e7e5e4;
}

/* ── TABEL DATA ── */
table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 8.5pt;
}
th {
  background: #f5f5f4;
  color: #44403c;
  font-weight: 700;
  font-size: 7.5pt;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 6px 8px;
  text-align: left;
  border-bottom: 1.5px solid #d6d3d1;
}
td {
  padding: 6px 8px;
  border-bottom: 1px solid #e7e5e4;
  color: #1c1917;
  vertical-align: middle;
}
tr:nth-child(even) td {
  background: #fafaf9;
}
.badge-hadir {
  display: inline-block;
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #bbf7d0;
  border-radius: 4px;
  padding: 1px 6px;
  font-size: 7.5pt;
  font-weight: 700;
}
.badge-lain {
  display: inline-block;
  background: #fffbeb;
  color: #b45309;
  border: 1px solid #fde68a;
  border-radius: 4px;
  padding: 1px 6px;
  font-size: 7.5pt;
  font-weight: 700;
}

/* ── BREAK & FOOTER ── */
.page-break { page-break-before: always; margin-top: 15px; }

.doc-footer {
  margin-top: 24px;
  border-top: 1px solid #e7e5e4;
  padding-top: 12px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  font-size: 8pt;
  color: #78716c;
}
.ttd-box { text-align: center; }
.ttd-line {
  border-top: 1px solid #1c1917;
  width: 170px;
  margin: 45px auto 4px;
}
.ttd-name { font-size: 8.5pt; color: #1c1917; font-weight: 700; }
.ttd-role { font-size: 7.5pt; color: #78716c; }
</style>
</head>
<body>

<!-- PRINT BAR (Hanya di layar monitor) -->
<div class="print-bar">
  <div>
    <div class="print-bar-title">Rekap Absensi — <?= $periode ?></div>
    <div class="print-bar-sub">Gunakan dialog cetak untuk mencetak fisik atau memilih opsi Simpan sebagai PDF</div>
  </div>
  <div style="display: flex; gap: 0.65rem; align-items: center;">
    <a href="rekap.php?bulan=<?= $bulan_sel ?>&tahun=<?= $tahun_sel ?>" class="btn-back-pdf">← Kembali</a>
    <button onclick="window.print()" class="btn-print">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 6 2 18 2 18 9"/>
        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
        <rect x="6" y="14" width="12" height="8"/>
      </svg>
      <span>Cetak / Simpan PDF</span>
    </button>
  </div>
</div>

<!-- DOKUMEN CETAK -->
<div class="page">

  <!-- Header Dokumen -->
  <div class="doc-header">
    <div>
      <div class="doc-logo">ABSENSI <span>ELITE</span></div>
      <div class="doc-subtitle">Sistem Pelaporan Absensi & Kehadiran Siswa</div>
    </div>
    <div class="doc-meta">
      <strong>REKAPITULASI RESMI BULANAN</strong><br>
      Dicetak oleh: <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?><br>
      Waktu Dokumen: <?= $generated ?> WIB
    </div>
  </div>

  <!-- Periode Rekap -->
  <div class="doc-period">
    <div class="doc-period-label">Periode Rekap Kehadiran</div>
    <div class="doc-period-val"><?= $periode ?></div>
  </div>

  <!-- Ringkasan Statistik -->
  <div class="stats-grid">
    <div class="stat-box">
      <div class="stat-box-num"><?= $total_siswa ?></div>
      <div class="stat-box-label">Siswa Terdata</div>
    </div>
    <div class="stat-box yellow">
      <div class="stat-box-num"><?= $total_absen ?></div>
      <div class="stat-box-label">Total Absensi</div>
    </div>
    <div class="stat-box orange">
      <div class="stat-box-num"><?= $total_hadir ?></div>
      <div class="stat-box-label">Kehadiran (Hadir)</div>
    </div>
    <div class="stat-box">
      <div class="stat-box-num"><?= $total_tidak_hadir ?></div>
      <div class="stat-box-label">Status Lainnya</div>
    </div>
  </div>

  <!-- Tabel 1: Ringkasan per Siswa -->
  <div class="section-title">Ringkasan Kehadiran per Siswa</div>
  <table>
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th>Nama Siswa</th>
        <th>Kelas</th>
        <th>Jurusan</th>
        <th style="text-align: center;">Total</th>
        <th style="text-align: center;">Hadir</th>
        <th style="text-align: center;">Lainnya</th>
        <th style="text-align: center;">Persentase</th>
      </tr>
    </thead>
    <tbody>
    <?php
    if(mysqli_num_rows($q_summary) == 0):
    ?>
      <tr><td colspan="8" style="text-align: center; color: #888; padding: 1rem;">Tidak ada data absensi pada periode ini.</td></tr>
    <?php
    else:
      $no = 1;
      mysqli_data_seek($q_summary, 0);
      while($row = mysqli_fetch_assoc($q_summary)):
        $total_row = (int)$row['total'];
        $hadir_row = (int)$row['hadir'];
        $pct = ($total_row > 0) ? round(($hadir_row / $total_row) * 100) : 0;
    ?>
      <tr>
        <td><?= $no++ ?></td>
        <td style="font-weight: 600;"><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['kelas']) ?></td>
        <td><?= htmlspecialchars($row['jurusan']) ?></td>
        <td style="text-align: center;"><?= $total_row ?></td>
        <td style="text-align: center;"><span class="badge-hadir"><?= $hadir_row ?></span></td>
        <td style="text-align: center;"><?= ($row['lainnya'] > 0) ? '<span class="badge-lain">'.$row['lainnya'].'</span>' : '—' ?></td>
        <td style="text-align: center; font-weight: 700;"><?= $pct ?>%</td>
      </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table>

  <!-- Tabel 2: Detail Seluruh Log Absensi -->
  <div class="page-break"></div>
  <div class="section-title" style="margin-top: 1rem;">Detail Transaksi Absensi Siswa</div>
  <table>
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th>Waktu Absensi</th>
        <th>Nama Siswa</th>
        <th>Kelas</th>
        <th>Jurusan</th>
        <th>Status</th>
        <th style="text-align: center;">Jarak (m)</th>
      </tr>
    </thead>
    <tbody>
    <?php
    if($total_absen == 0):
    ?>
      <tr><td colspan="7" style="text-align: center; color: #888; padding: 1rem;">Tidak ada rincian data absensi.</td></tr>
    <?php
    else:
      mysqli_data_seek($q_detail, 0);
      $no2 = 1;
      while($row = mysqli_fetch_assoc($q_detail)):
        $tgl_format = (!empty($row['tanggal'])) ? date('d M Y, H:i', strtotime($row['tanggal'])) : '—';
        $isHadir = (strtolower($row['status']) === 'hadir');
    ?>
      <tr>
        <td><?= $no2++ ?></td>
        <td style="white-space: nowrap;"><?= $tgl_format ?></td>
        <td style="font-weight: 600;"><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['kelas']) ?></td>
        <td><?= htmlspecialchars($row['jurusan']) ?></td>
        <td><?= $isHadir ? '<span class="badge-hadir">Hadir</span>' : '<span class="badge-lain">'.htmlspecialchars($row['status']).'</span>' ?></td>
        <td style="text-align: center;"><?= htmlspecialchars($row['jarak']) ?></td>
      </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table>

  <!-- Tanda Tangan & Footer -->
  <div class="doc-footer">
    <div>
      Laporan dicetak secara digital melalui sistem manajemen ABSENSI ELITE.<br>
      <?= $generated ?> WIB
    </div>
    <div class="ttd-box">
      <div class="ttd-line"></div>
      <div class="ttd-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?></div>
      <div class="ttd-role">Petugas / Administrator Sistem</div>
    </div>
  </div>

</div>

<script>
window.addEventListener('load', function(){
  setTimeout(function(){ window.print(); }, 800);
});
</script>
</body>
</html>
