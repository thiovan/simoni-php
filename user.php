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

if ($_SESSION['user_role'] != 'admin') {
  header("Location: dashboard.php");
  exit();
}

// Ambil data semua pengguna
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['fullname']) && isset($_POST['role'])) {
  // Ambil data dari form
  $username = $_POST['username'];
  $password = $_POST['password'];
  $fullname = $_POST['fullname'];
  $role = $_POST['role'];

  // Enkripsi password
  $password = password_hash($password, PASSWORD_DEFAULT);

  // Simpan data pengguna ke database
  $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, role) VALUES (:username, :password, :fullname, :role)");
  $stmt->bindParam(':username', $username); 
  $stmt->bindParam(':password', $password);
  $stmt->bindParam(':fullname', $fullname);
  $stmt->bindParam(':role', $role);
  $stmt->execute();

  // Redirect ke halaman user.php
  header("Location: user.php");
  exit();
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
        <a href="dashboard.php" class="nav-link link-body-emphasis" aria-current="page">
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
        <a href="user.php" class="nav-link active bg-danger">
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
          <h4 class="card-title text-center mb-0">KELOLA PENGGUNA</h4>
        </div>
      </div>

      <div class="card my-4">
        <div class="card-header">
          <form action="user.php" method="post">
            <h5>Tambah Pengguna</h5>
            <div class="row">
              <div class="col col-12 col-md-6 mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
              </div>
              <div class="col col-12 col-md-6 mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
              </div>
            </div>
            <div class="row">
              <div class="col col-12 col-md-6 mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="fullname" required>
              </div>
              <div class="col col-12 col-md-6 mb-3">
                <label class="form-label">Peran</label>
                <select class="form-select" name="role" required>
                  <option value="admin">Admin</option>
                  <option value="officer">Officer</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col col-12">
                <button type="submit" class="btn btn-primary btn-secondary w-100">Tambah Pengguna</button>
              </div>
            </div>
          </form>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered mt-3">
              <thead>
                <tr>
                  <th class="text-center align-middle">No</th>
                  <th class="text-center align-middle">Username</th>
                  <th class="text-center align-middle">Nama</th>
                  <th class="text-center align-middle">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $index => $user) { ?>
                  <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td class="text-center">
                      <span class="badge rounded-pill text-bg-danger"><?php echo htmlspecialchars($user['role']); ?></span>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
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
  </script>

</body>

</html>