<?php
session_start();
$koneksi_path = file_exists(__DIR__ . '/../config/koneksi.php') 
    ? __DIR__ . '/../config/koneksi.php' 
    : $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';
include $koneksi_path;

$error = '';
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);
    if($data){
        if(password_verify($password, $data['password'])){
            $_SESSION['login'] = true;
            $_SESSION['nama']  = $data['nama'];
            $_SESSION['id']    = $data['id'];
            header("Location: dashboard.php");
            exit;
        } else { 
            $error = "Password yang Anda masukkan salah. Coba lagi."; 
        }
    } else { 
        $error = "Username tidak ditemukan dalam sistem."; 
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — ABSENSI ELITE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css?v=<?= time() ?>">
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card-modern">
    <!-- Header Banner Oranye & Kuning Cerah -->
    <div class="auth-header-banner">
      <div class="auth-banner-logo">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
          <line x1="16" x2="16" y1="2" y2="6"/>
          <line x1="8" x2="8" y1="2" y2="6"/>
          <line x1="3" x2="21" y1="10" y2="10"/>
          <path d="m9 16 2 2 4-4"/>
        </svg>
      </div>
      <div class="auth-banner-brand">ABSENSI <span>ELITE</span></div>
      <div class="auth-banner-desc">Sistem Absensi Kehadiran Siswa</div>
    </div>

    <!-- Form Body -->
    <div class="auth-body">
      <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 0.25rem; color: var(--text);">
        Selamat Datang
      </h2>
      <p style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 1.35rem;">
        Masuk dengan akun siswa untuk melakukan absensi
      </p>

      <?php if(!empty($error)): ?>
      <div class="alert alert-danger" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" x2="12" y1="8" y2="12"/>
          <line x1="12" x2="12.01" y1="16" y2="16"/>
        </svg>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-with-icon">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="username" name="username" placeholder="Masukkan username akun" required autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-with-icon">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" name="login" class="btn btn-primary btn-block" style="margin-top: 0.65rem;">
          <span>Masuk ke Akun</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/>
            <path d="m12 5 7 7-7 7"/>
          </svg>
        </button>
      </form>

      <div class="auth-divider">atau</div>

      <a href="register.php" class="btn btn-secondary btn-block">
        Belum punya akun? Daftar sekarang
      </a>
    </div>
  </div>
</div>

</body>
</html>
