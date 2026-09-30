<?php
$koneksi_path = file_exists(__DIR__ . '/../config/koneksi.php') 
    ? __DIR__ . '/../config/koneksi.php' 
    : $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';
include $koneksi_path;

ini_set('display_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['submit'])){
    $nama     = $_POST['nama'];
    $kelas    = $_POST['kelas'];
    $jurusan  = $_POST['jurusan'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn,
        "INSERT INTO users (nama,kelas,jurusan,username,password)
         VALUES ('$nama','$kelas','$jurusan','$username','$password')"
    );

    echo "<script>alert('Pendaftaran berhasil. Silakan masuk dengan akun kamu.');window.location='login.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun Siswa — ABSENSI ELITE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css?v=<?= time() ?>">
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card-modern" style="max-width: 480px;">
    <!-- Header Banner Oranye & Kuning Cerah -->
    <div class="auth-header-banner">
      <div class="auth-banner-logo">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="19" x2="19" y1="8" y2="14"/>
          <line x1="22" x2="16" y1="11" y2="11"/>
        </svg>
      </div>
      <div class="auth-banner-brand">ABSENSI <span>ELITE</span></div>
      <div class="auth-banner-desc">Registrasi Akun Siswa Baru</div>
    </div>

    <!-- Form Body -->
    <div class="auth-body">
      <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 0.25rem; color: var(--text);">
        Buat Akun Baru
      </h2>
      <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 1.35rem;">
        Daftarkan dirimu sebagai siswa untuk mulai menggunakan sistem absensi
      </p>

      <form method="POST" action="">
        <!-- Nama Lengkap -->
        <div class="form-group">
          <label class="form-label" for="nama">Nama Lengkap</label>
          <div class="input-with-icon">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="nama" name="nama" placeholder="Contoh: Muhammad Junior" required>
          </div>
        </div>

        <!-- Kelas & Jurusan berdampingan -->
        <div class="grid-2" style="gap: 0.85rem; margin-bottom: 1.15rem;">
          <div>
            <label class="form-label" for="kelas">Kelas</label>
            <select id="kelas" name="kelas" required>
              <option value="">Pilih Kelas</option>
              <option value="X">Kelas X</option>
              <option value="XI">Kelas XI</option>
              <option value="XII">Kelas XII</option>
            </select>
          </div>
          <div>
            <label class="form-label" for="jurusan">Jurusan</label>
            <select id="jurusan" name="jurusan" required>
              <option value="">Pilih Jurusan</option>
              <option value="TAV">TAV</option>
              <option value="TITL">TITL</option>
              <option value="TKR">TKR</option>
              <option value="TKJ">TKJ</option>
              <option value="DPIB">DPIB</option>
              <option value="TKP">TKP</option>
              <option value="RPL">RPL</option>
              <option value="TSM">TSM</option>
              <option value="TEI">TEI</option>
              <option value="ANI">ANI</option>
              <option value="TPM">TPM</option>
            </select>
          </div>
        </div>

        <!-- Username -->
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-with-icon">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="4"/>
              <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/>
            </svg>
            <input type="text" id="username" name="username" placeholder="Buat username tanpa spasi" required autocomplete="username">
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-with-icon">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" placeholder="Buat password yang aman" required autocomplete="new-password">
          </div>
        </div>

        <button type="submit" name="submit" class="btn btn-primary btn-block" style="margin-top: 0.65rem;">
          <span>Daftar Sekarang</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/>
            <path d="m12 5 7 7-7 7"/>
          </svg>
        </button>
      </form>

      <div class="auth-divider">atau</div>

      <a href="login.php" class="btn btn-secondary btn-block">
        Sudah punya akun? Masuk
      </a>
    </div>
  </div>
</div>

</body>
</html>