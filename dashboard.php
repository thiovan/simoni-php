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
$stmt = $conn->prepare("SELECT * FROM complaints");
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

      <!-- Menampilkan Logo Kecamatan Gajahmungkur -->
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="assets/images/logo-pemkot.png" width="36">

        <div class="h5 ms-2 d-none d-md-block">Kecamatan Gajahmungkur</div>
        <div class="h6 ms-2 mb-0 d-block d-md-none">Kecamatan <br>Gajahmungkur</div>
      </a>
      <!-- / Menampilkan Logo Kecamatan Gajahmungkur -->

      <!-- Menampilkan Dropdown Profile -->
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
      <!-- / Menampilkan Dropdown Profile -->

    </div>
  </nav>
  <!-- / Menampilkan Navigasi -->

  <!-- Menampilkan Konten -->
  <main class="container mt-5">
    <h4 class="text-center">Daftar Aduan</h4>
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
                  <div class="col">
                    <a href="detail.php?ticket=<?php echo $complaint['ticket']; ?>" class="btn btn-primary btn-sm w-100" target="_blank"><i class="bi bi-pencil"></i> Input</a>
                  </div>
                  <div class="col">
                    <a href="https://api.whatsapp.com/send?phone=<?php echo preg_replace('/^0/', '62', $complaint['whatsapp']); ?>&text=Halo%2C+saya+menghubungi+anda+terkait+aduan+<?php echo htmlspecialchars($complaint['ticket']); ?>" class="btn btn-success btn-sm w-100" target="_blank"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                  </div>
                  <div class="col">
                    <a href="mailto:<?php echo $complaint['email']; ?>?subject=Aduan: <?php echo htmlspecialchars($complaint['ticket']); ?>" class="btn btn-info btn-sm w-100" target="_blank"><i class="bi bi-envelope"></i> Email</a>
                  </div>
                  <div class="col">
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
  </main>
  <!-- / Menampilkan Konten -->

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