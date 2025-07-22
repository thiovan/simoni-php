<?php
require_once 'config.php';
session_start();

try {
  // Koneksi ke database
  $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  // Jika gagal, maka tampilkan pesan error
  die("Koneksi gagal: " . $e->getMessage());
}

// Ambil jumlah aduan
if ($_SESSION['user_role'] == 'admin') {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints");
} else {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE officer_id = :officer_id");
  $stmt->bindParam(':officer_id', $_SESSION['user_id']);
}
$stmt->execute();
$complaint_total = $stmt->fetchColumn();

// Ambil jumlah aduan yang masih dalam proses penanganan
if ($_SESSION['user_role'] == 'admin') {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE status_id IN (1, 2, 3, 4, 5)");
} else {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE status_id IN (1, 2, 3, 4, 5) AND officer_id = :officer_id");
  $stmt->bindParam(':officer_id', $_SESSION['user_id']);
}
$stmt->execute();
$complaint_pending = $stmt->fetchColumn();

// Ambil jumlah aduan yang sudah selesai
if ($_SESSION['user_role'] == 'admin') {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE status_id IN (2, 6)");
} else {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE status_id IN (2, 6) AND officer_id = :officer_id");
  $stmt->bindParam(':officer_id', $_SESSION['user_id']);
}
$stmt->execute();
$complaint_closed = $stmt->fetchColumn();

// Ambil data aduan terakhir 6 bulan
$currentYear = date('Y');
$currentMonth = date('n');
$complaint_last_6_month = array();
for ($i = 0; $i < 6; $i++) {
  if ($_SESSION['user_role'] == 'admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE MONTH(created_at) = :month AND YEAR(created_at) = :year");
  } else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM complaints WHERE MONTH(created_at) = :month AND YEAR(created_at) = :year AND officer_id = :officer_id");
    $stmt->bindParam(':officer_id', $_SESSION['user_id']);
  }
  $stmt->bindParam(':month', $currentMonth);
  $stmt->bindParam(':year', $currentYear);
  $stmt->execute();
  $months = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
  $monthName = $months[$currentMonth - 1];
  $complaint_last_6_month[$monthName . ' ' . $currentYear] = $stmt->fetchColumn();
  $currentMonth--;
  if ($currentMonth == 0) {
    $currentMonth = 12;
    $currentYear--;
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?php echo APP_NAME; ?></title>

  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/custom.css">

  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/chart.js"></script>
</head>

<body class="d-flex">

  <!-- Menampilkan Sidebar -->
  <div id="sidebar" class="collapse c collapse-horizontal p-3 bg-body-tertiary" style="width: 280px;">
    <a href="" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none mx-auto">
      <img src="assets/images/logo.png" width="200">
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="dashboard.php" class="nav-link active bg-danger" aria-current="page">
          <i class="bi bi-house-door pe-none me-2"></i>
          Dashboard
        </a>
      </li>
      <li>
        <a href="complaint.php" class="nav-link link-body-emphasis">
          <i class="bi bi-inboxes pe-none me-2"></i>
          Kelola Aduan
        </a>
      </li>
      <li class="<?php echo $_SESSION['user_role'] != 'admin' ? 'd-none' : ''; ?>">
        <a href="user.php" class="nav-link link-body-emphasis">
          <i class="bi bi-people pe-none me-2"></i>
          Kelola Pengguna
        </a>
      </li>
    </ul>

  </div>
  <!-- / Menampilkan Sidebar -->

  <div class="container-fluid px-0">

    <!-- Menampilkan Navigasi -->
    <nav class="navbar bg-body-tertiary shadow-sm border-bottom">
      <div class="container">

        <div>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="true" aria-label="Toggle Sidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>

        <!-- Menampilkan Dropdown Profile -->
        <div>
          <div class="dropdown text-end">
            <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="assets/images/icon-user.png" alt="mdo" width="32" height="32" class="rounded-circle">
            </a>
            <?php if (isset($_SESSION['user_id'])) { ?>
              <ul class="dropdown-menu text-small dropdown-menu-end">
                <li>
                  <div class="dropdown-item disabled text-muted fw-bold"><?php echo $_SESSION['user_fullname'] ?></div>
                </li>
                <li><small class="dropdown-item disabled text-muted"><span class="badge rounded-pill text-bg-secondary">&nbsp;<?php echo $_SESSION['user_username'] ?>&nbsp;</span></small></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                <li><a class="dropdown-item text-danger" href="index.php?logout">Keluar</a></li>
              </ul>
            <?php } else { ?>
              <ul class="dropdown-menu text-small dropdown-menu-end">
                <li>
                <li><a class="dropdown-item" href="login.php">Login Admin</a></li>
                </li>
              </ul>
            <?php } ?>
          </div>
        </div>
        <!-- / Menampilkan Dropdown Profile -->

      </div>
    </nav>
    <!-- / Menampilkan Navigasi -->

    <!-- Menampilkan Konten -->
    <main class="container mt-5 min-vh-100">

      <div class="card my-3 shadow-sm">
        <div class="card-body">
          <h4 class="card-title text-center mb-0">DASHBOARD</h4>
        </div>
      </div>

      <div class="row">
        <div class="col col-12 col-md-4">
          <div class="card my-3 shadow-sm">
            <div class="card-body text-center">
              <h5 class="card-title">Aduan Masuk</h5>
              <p class="display-4 fw-bold"><?php echo $complaint_total; ?></p>
            </div>
          </div>
        </div>

        <div class="col col-12 col-md-4">
          <div class="card my-3 shadow-sm">
            <div class="card-body text-center">
              <h5 class="card-title">Aduan Diproses</h5>
              <p class="display-4 fw-bold"><?php echo $complaint_pending; ?></p>
            </div>
          </div>
        </div>

        <div class="col col-12 col-md-4">
          <div class="card my-3 shadow-sm">
            <div class="card-body text-center">
              <h5 class="card-title">Aduan Selesai</h5>
              <p class="display-4 fw-bold"><?php echo $complaint_closed; ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="card my-3 shadow-sm">
        <div class="card-body">
          <canvas class="w-100" id="chart" style="height: 350px;"></canvas>
        </div>
      </div>

    </main>
    <!-- / Menampilkan Konten -->

    <!-- Menampilkan Footer -->
    <footer class="mt-auto border-top shadow-sm">
      <ul class="nav border-bottom pb-3 mb-3">
      </ul>
      <p class="text-center text-body-secondary mb-0 pb-3">Copyright &copy; 2025. Developed by <?php echo APP_AUTHOR; ?></p>
    </footer>
    <!-- / Menampilkan Footer -->

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const collapseElement = document.getElementById('sidebar');
      const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

      if (isMobile) {
        // Collapse the element on mobile
        const collapse = new bootstrap.Collapse(collapseElement, {
          toggle: false
        });
        collapse.hide();
      } else {
        // Show the element on md and larger screens
        const collapse = new bootstrap.Collapse(collapseElement, {
          toggle: false
        });
        collapse.show();
      }
    });

    const ctx = document.getElementById('chart');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_keys($complaint_last_6_month)); ?>,
        datasets: [{
          label: 'Jumlah Aduan',
          data: <?php echo json_encode(array_values($complaint_last_6_month)); ?>,
          borderWidth: 1,
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderColor: 'rgba(255, 99, 132, 1)'
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>

</body>

</html>