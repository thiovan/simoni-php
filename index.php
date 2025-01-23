<?php
require_once 'config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/custom.css">
  <title><?php echo APP_NAME; ?></title>
</head>

<body>

  <!-- Menampilkan Navigasi -->
  <nav class="navbar bg-body-tertiary">
    <div class="container">

      <!-- Logo -->
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="assets/images/logo-pemkot.png" width="36"> 
        
        <div class="h5 ms-2 d-none d-md-block">Kecamatan Gajahmungkur</div>
        <div class="h6 ms-2 mb-0 d-block d-md-none">Kecamatan <br>Gajahmungkur</div>
      </a>

      <!-- Menampilkan Dropdown Profile -->
      <div class="dropdown text-end">
        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="assets/images/icon-user.png" alt="mdo" width="32" height="32" class="rounded-circle">
        </a>
        <ul class="dropdown-menu text-small">
          <li>
            <div class="dropdown-item disabled text-black"><?php echo $_SESSION['user_fullname'] ?></div>
          </li>
          <li><small class="dropdown-item disabled text-black"><?php echo $_SESSION['user_role'] ?></small></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item text-danger" href="/logout">Keluar</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- / Menampilkan Navigasi -->

  <main class="container mt-5">
    <div class="text-center">
      <img src="assets/images/logo.png" alt="Logo Kelurahan Lempongsari" width="300">
      <p class="display-6 mt-3 mb-0 d-none d-md-block">Portal Pengaduan Online</p>
      <p class="h3 mt-3 mb-0 d-block d-md-none">Portal Pengaduan Online</p>
      <p class="display-6 d-none d-md-block">Kecamatan Gajahmungkur</p>
      <p class="h3 d-block d-md-none">Kecamatan Gajahmungkur</p>
    </div>


    <div class="mb-4 mt-5">
      <small class="form-label d-block d-md-none">Sudah pernah lapor aduan? Lacak aduan anda disini.</small>
      <label class="form-label d-none d-md-block">Sudah pernah lapor aduan? Lacak aduan anda disini.</label>
      <div class="input-group">
        <input type="text" class="form-control form-control-lg" placeholder="Ketikan kode tiket aduan...">
        <button class="btn btn-danger px-4"><i class="bi bi-search"></i> Lacak</button>
      </div>
      <small class="text-danger">Contoh: SML-123456</small>
    </div>

    <div class="card mb-4">
      <div class="card-header text-center">
        <h4>Formulir Aduan</h4>
      </div>
      <div class="card-body">
        <form>
          <div class="mb-3">
            <label for="whatsapp" class="form-label">Nomor WhatsApp</label>
            <input type="text" class="form-control" id="whatsapp" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" required>
          </div>
          <div class="mb-3">
            <label for="complaint" class="form-label">Isi Aduan</label>
            <textarea class="form-control" id="complaint" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="location" class="form-label">Lokasi Aduan / Kejadian</label>
            <textarea class="form-control" id="location" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label for="file" class="form-label">Lampiran Foto</label>
            <input type="file" class="form-control" id="file">
          </div>
          <button type="submit" class="btn btn-danger w-100"><i class="bi bi-send"></i> Kirim Aduan</button>
        </form>
      </div>
    </div>
  </main>

  <!-- Menampilkan Footer -->
  <footer class="fixed-bottom w-100">
    <ul class="nav border-bottom pb-3 mb-3">
    </ul>
    <p class="text-center text-body-secondary">Copyright &copy; 2025. Developed by <?php echo APP_AUTHOR; ?></p>
  </footer>
  <!-- / Menampilkan Footer -->

  <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>