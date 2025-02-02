<?php
require_once 'config.php';
session_start();

// Membuat koneksi ke database
try {
  $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage()); // Menangani kegagalan koneksi
}

// Fungsi logout, menghapus session dan mengarahkan ke halaman login
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: login.php");
}

// Fungsi untuk membuat aduan baru
function createComplaint($conn, $whatsapp, $email, $description, $location, $images)
{
  // Membuat kode tiket unik
  do {
    $ticket = "SMN-" . sprintf("%06d", mt_rand(0, 999999)); // Membuat format tiket dengan angka acak
    $stmt = $conn->prepare("SELECT ticket FROM complaints WHERE ticket = :ticket");
    $stmt->bindParam(':ticket', $ticket);
    $stmt->execute();
  } while ($stmt->rowCount() > 0); // Ulangi sampai mendapatkan tiket yang unik

  // Simpan data aduan ke database
  $stmt = $conn->prepare("INSERT INTO complaints (ticket, whatsapp, email, description, location, created_at) VALUES (:ticket, :whatsapp, :email, :description, :location, NOW())");
  $stmt->bindParam(':ticket', $ticket);
  $stmt->bindParam(':whatsapp', $whatsapp);
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':description', $description);
  $stmt->bindParam(':location', $location);
  $stmt->execute();
  $complaint_id = $conn->lastInsertId(); // Dapatkan ID aduan yang baru dimasukkan

  // Simpan data riwayat aduan ke database
  $stmt = $conn->prepare("INSERT INTO histories (complaint_id, created_at) VALUES (:complaint_id, NOW())");
  $stmt->bindParam(':complaint_id', $complaint_id);
  $stmt->execute();
  $history_id = $conn->lastInsertId(); // Dapatkan ID riwayat yang baru dimasukkan

  // Simpan lampiran foto
  if (!empty($images['name'][0])) {
    for ($i = 0; $i < count($images['name']); $i++) {

      // Periksa apakah file yang diunggah adalah gambar
      $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
      $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
      if (!in_array($ext, $allowTypes)) {
        continue;
      }

      $imageName = $ticket . '-' . '0' . '_' . ($i + 1) . '.' . $ext;
      $imagePath = 'uploads/' . $imageName;

      // Simpan lampiran foto ke direktori
      move_uploaded_file($images['tmp_name'][$i], $imagePath);

      // Simpan lampiran foto ke database
      $stmt = $conn->prepare("INSERT INTO images (history_id, filename) VALUES (:history_id, :filename)");
      $stmt->bindParam(':history_id', $history_id);
      $stmt->bindParam(':filename', $imageName);
      $stmt->execute();
    }
  }

  // Tambahkan entri riwayat untuk status awal
  $status_id = "1"; // Anggap 1 adalah ID untuk status awal
  $notes = "Terima kasih telah menggunakan Si Moni (Sistem Monitoring Aduan Gajahmungkur). Aduan anda akan segera kami tindak lanjuti.";
  $stmt = $conn->prepare("INSERT INTO histories (complaint_id, status_id, notes, created_at) VALUES (:complaint_id, :status_id, :notes, NOW())");
  $stmt->bindParam(':complaint_id', $complaint_id);
  $stmt->bindParam(':status_id', $status_id);
  $stmt->bindParam(':notes', $notes);
  $stmt->execute();

  return $ticket; // Kembalikan kode tiket
}

// Memeriksa apakah semua data yang diperlukan telah di-post
if (isset($_POST['whatsapp']) && isset($_POST['email']) && isset($_POST['description']) && isset($_POST['location'])) {
  // Mengambil data dari form
  $whatsapp = $_POST['whatsapp'];
  $email = $_POST['email'];
  $description = $_POST['description'];
  $location = $_POST['location'];
  $images = $_FILES['images'];

  // Membuat aduan baru dan mendapatkan nomor tiket
  $ticket = createComplaint($conn, $whatsapp, $email, $description, $location, $images);

  // Mengarahkan pengguna ke halaman detail aduan
  header("Location: detail.php?ticket=$ticket");
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

    <!-- Menampilkan Logo dan Judul Aplikasi -->
    <div class="text-center">
      <img src="assets/images/logo.png" alt="Logo Kelurahan Lempongsari" width="300">
      <p class="display-6 mt-3 mb-0 d-none d-md-block">Portal Pengaduan Online</p>
      <p class="h3 mt-3 mb-0 d-block d-md-none">Portal Pengaduan Online</p>
      <p class="display-6 d-none d-md-block">Kecamatan Gajahmungkur</p>
      <p class="h3 d-block d-md-none">Kecamatan Gajahmungkur</p>
    </div>
    <!-- / Menampilkan Logo dan Judul Aplikasi -->


    <!-- Menampilkan Lacak Tiket Aduan -->
    <div class="card mb-4 mt-5">
      <div class="card-body">
        <small class="form-label d-block d-md-none">Sudah pernah lapor aduan? Lacak aduan anda disini.</small>
        <label class="form-label d-none d-md-block">Sudah pernah lapor aduan? Lacak aduan anda disini.</label>
        <form action="detail.php" method="get">
          <div class="input-group">
            <input type="text" class="form-control form-control-lg" name="ticket" placeholder="Ketikan kode tiket aduan...">
            <button type="submit" class="btn btn-danger px-4"><i class="bi bi-search"></i> Lacak</button>
          </div>
        </form>
        <small class="text-danger">Contoh: SML-123456</small>
      </div>
    </div>
    <!-- / Menampilkan Lacak Tiket Aduan -->

    <!-- Menampilkan Formulir Aduan -->
    <div class="card mb-4">
      <div class="card-header text-center">
        <h4>Formulir Aduan</h4>
      </div>
      <div class="card-body">
        <form action="index.php" method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="whatsapp" class="form-label">Nomor WhatsApp</label>
            <input type="text" class="form-control" name="whatsapp" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Isi Aduan</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="location" class="form-label">Lokasi Aduan / Kejadian</label>
            <textarea class="form-control" name="location" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label for="file" class="form-label">Lampiran Foto</label>
            <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
            <small>Format: .jpg, .jpeg, .png - Bisa memilih lebih dari 1 gambar</small>
          </div>
          <button type="submit" class="btn btn-danger w-100 mt-2"><i class="bi bi-send"></i> Kirim Aduan</button>
        </form>
      </div>
    </div>
    <!-- / Menampilkan Formulir Aduan -->

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