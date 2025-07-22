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

// Ambil semua data aduan dari tabel complaints
if ($_SESSION['user_role'] == 'admin') {
  $stmt = $conn->prepare("SELECT * FROM complaints ORDER BY created_at DESC");
} else {
  $stmt = $conn->prepare("SELECT * FROM complaints WHERE officer_id = :officer_id ORDER BY created_at DESC");
  $stmt->bindParam(':officer_id', $_SESSION['user_id']);
}
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Looping untuk mengambil data terakhir dari setiap aduan
for ($i = 0; $i < count($complaints); $i++) {
  // Ambil data terakhir dari setiap aduan
  $stmt = $conn->prepare("SELECT h.created_at AS history_created_at, s.name AS status_name FROM histories h JOIN statuses s ON h.status_id = s.id WHERE h.complaint_id = :complaint_id ORDER BY h.created_at DESC LIMIT 1");
  $stmt->bindParam(':complaint_id', $complaints[$i]['id']);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  // Simpan data terakhir ke dalam array $complaints
  $complaints[$i]['last_status'] = $result['status_name'];
  $complaints[$i]['last_status_date'] = $result['history_created_at'];
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
        <a href="complaint.php" class="nav-link active bg-danger">
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

      <div class="card my-4 shadow-sm">
        <div class="card-body">
          <h4 class="card-title text-center mb-0">KELOLA ADUAN</h4>
        </div>
      </div>

      <div class="card my-3">
        <div class="card-header">
          <form action="export.php" method="get">
            <div class="row">
              <div class="col col-12 col-md-6">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="from" required>
              </div>
              <div class="col col-12 col-md-6">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="to" required>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col col-12">
                <button type="submit" class="btn btn-primary btn-secondary w-100">Export Laporan</button>
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
                  <th class="text-center align-middle">Waktu</th>
                  <th class="text-center align-middle">Kode Tiket</th>
                  <th class="text-center align-middle">Deskripsi</th>
                  <th class="text-center align-middle">Lokasi</th>
                  <th class="text-center align-middle">Status Terakhir</th>
                  <th class="text-center align-middle">Opsi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($complaints as $index => $complaint) { ?>
                  <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td class="text-center"><?php echo date('d M Y, H:i', strtotime($complaint['last_status_date'])); ?> WIB</td>
                    <td class="text-center"><?php echo htmlspecialchars($complaint['ticket']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars(mb_strimwidth($complaint['description'], 0, 50, '...')); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars(mb_strimwidth($complaint['location'], 0, 50, '...')); ?></td>
                    <td class="text-center"><?php echo ucwords($complaint['last_status']); ?></td>
                    <td class="text-center">

                      <div class="row row-cols-2 g-2">
                        <div class="col col-12 col-md-6">
                          <a href="detail.php?ticket=<?php echo $complaint['ticket']; ?>" class="btn btn-primary btn-sm w-100" target="_blank"><i class="bi bi-pencil"></i> Input</a>
                        </div>
                        <div class="col col-12 col-md-6">
                          <a href="https://api.whatsapp.com/send?phone=<?php echo preg_replace('/^0/', '62', $complaint['whatsapp']); ?>&text=Halo%2C+saya+menghubungi+anda+terkait+aduan+<?php echo htmlspecialchars($complaint['ticket']); ?>" class="btn btn-success btn-sm w-100" target="_blank"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                        </div>
                        <div class="col col-12 col-md-6">
                          <a href="mailto:<?php echo $complaint['email']; ?>?subject=Aduan: <?php echo htmlspecialchars($complaint['ticket']); ?>" class="btn btn-info btn-sm w-100" target="_blank"><i class="bi bi-envelope"></i> Email</a>
                        </div>
                        <div class="col col-12 col-md-6">
                          <a href="print.php/?id=<?php echo $complaint['id']; ?>" class="btn btn-secondary btn-sm w-100" target="_blank"><i class="bi bi-printer"></i> Cetak</a>
                        </div>
                      </div>

                      <a href="delete.php?id=<?php echo $complaint['id']; ?>" class="btn btn-danger btn-sm w-100 mt-2" onclick="return confirm('Apakah Anda yakin ingin menghapus data aduan ini?')"><i class="bi bi-trash"></i> Hapus</a>
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