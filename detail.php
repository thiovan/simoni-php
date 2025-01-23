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

  <div class="container mt-5">
    <h1 class="text-center display-6 mb-4">Detail Aduan</h1>

    <div class="card mb-4">
      <div class="card-body">
        <h5 class="text-center mb-3">Status Aduan</h5>
        <div class="stepper-wrapper">
          <div class="stepper-item active">
            <div class="step-counter">1</div>
            <div class="step-name">Verifikasi</div>
          </div>
          <div class="stepper-item">
            <div class="step-counter">2</div>
            <div class="step-name">Tindak Lanjut</div>
          </div>
          <div class="stepper-item">
            <div class="step-counter">3</div>
            <div class="step-name">Selesai</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Kode Tiket: <span class="text-danger">SML-904751</span></h5>
          <span class="badge rounded-pill text-bg-danger">Verifikasi</span>
        </div>
        <hr>
        <b>19 Mar 2024, 14:51 WIB</b>
        <p>belakang rumah saya kena pohon tumbang akibat longsor sejak Rabu, 13 Maret 2024...</p>
        <b>Lokasi:</b>
        <p>Jalan Lempongsari I gang buntu No. 401L Semarang</p>
        <b>Catatan:</b>

        <div class="accordion accordion-flush" id="accordionExample">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-controls="collapseOne">
                <span class="badge rounded-pill text-bg-danger">Verifikasi</span>
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show">
              <div class="accordion-body">
                <b>19 Mar 2024, 14:51 WIB</b>
                <p>Terima kasih telah menggunakan Portal Aduan Sapa Mas Lurah Lempongsari. Aduan anda akan segera kami tindak lanjut.</p>
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                Accordion Item #2
              </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse show">
              <div class="accordion-body">
                <strong>This is the second item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

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