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

// Jika ada input post, maka simpan data tindakan ke database
if (isset($_POST['ticket']) && isset($_POST['complaint_id']) && isset($_POST['status_id']) && isset($_POST['notes'])) {
  // Ambil data yang dikirim melalui post
  $ticket = $_POST['ticket'];
  $complaint_id = $_POST['complaint_id'];
  $status_id = $_POST['status_id'];
  $notes = $_POST['notes'];

  // Jika ada data petugas, maka simpan data petugas ke database
  if (isset($_POST['officer_id'])) {
    $officer_id = $_POST['officer_id'];
    $stmt = $conn->prepare("UPDATE complaints SET officer_id = :officer_id, status_id = :status_id WHERE id = :complaint_id");
    $stmt->bindParam(':officer_id', $officer_id);
    $stmt->bindParam(':status_id', $status_id);
    $stmt->bindParam(':complaint_id', $complaint_id);
    $stmt->execute();
  } else {
    $stmt = $conn->prepare("UPDATE complaints SET status_id = :status_id WHERE id = :complaint_id");
    $stmt->bindParam(':status_id', $status_id);
    $stmt->bindParam(':complaint_id', $complaint_id);
    $stmt->execute();
  }

  // Query untuk menyimpan data tindakan ke database
  $stmt = $conn->prepare("INSERT INTO histories (complaint_id, status_id, notes, created_at) VALUES (:complaint_id, :status_id, :notes, NOW())");
  $stmt->bindParam(':complaint_id', $complaint_id);
  $stmt->bindParam(':status_id', $status_id);
  $stmt->bindParam(':notes', $notes);
  $stmt->execute();
  $history_id = $conn->lastInsertId();

  // Jika ada lampiran foto, maka simpan lampiran foto ke direktori dan database
  if (!empty($_FILES['images']['name'][0])) {
    $images = $_FILES['images'];
    for ($i = 0; $i < count($images['name']); $i++) {

      // Periksa apakah file yang diunggah adalah gambar
      $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
      $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
      if (!in_array($ext, $allowTypes)) {
        continue;
      }

      $imageName = $ticket . '-' . $status_id . '_' . ($i + 1) . '.' . $ext;
      $imagePath = 'uploads/' . $imageName;

      // Simpan lampiran foto ke direktori
      move_uploaded_file($images['tmp_name'][$i], $imagePath);

      // Simpan lampiran foto ke database
      $stmt = $conn->prepare("INSERT INTO images (source_type, source_id, filename) VALUES ('history', :source_id, :filename)");
      $stmt->bindParam(':source_id', $history_id);
      $stmt->bindParam(':filename', $imageName);
      $stmt->execute();
    }
  }

  // Jika berhasil, maka redirect ke halaman detail
  header("Location: detail.php?ticket=$ticket");
  exit;
}

// Jika ada input get, maka ambil data aduan berdasarkan ticket yang dikirim
if (isset($_GET['ticket'])) {
  $ticket = $_GET['ticket'];
  $stmt = $conn->prepare("SELECT complaints.*, users.fullname AS officer FROM complaints LEFT JOIN users ON complaints.officer_id = users.id WHERE ticket = :ticket");
  $stmt->bindParam(':ticket', $ticket);
  $stmt->execute();
  $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

  // Jika tidak ada data aduan, maka redirect ke halaman index
  if (!$complaint) {
    header("Location: index.php");
    exit;
  }

  // Ambil semua data lampiran foto
  $stmt = $conn->prepare("SELECT * FROM images WHERE source_type = 'complaint' AND source_id = :complaint_id");
  $stmt->bindParam(':complaint_id', $complaint['id']);
  $stmt->execute();
  $complaint['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Ambil semua data status
  $stmt = $conn->prepare("SELECT * FROM statuses ORDER BY `order` ASC");
  $stmt->execute();
  $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Ambil semua data petugas
  $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'officer' ORDER BY fullname ASC");
  $stmt->execute();
  $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Ambil semua data tindakan aduan berdasarkan id yang dikirim
  $stmt = $conn->prepare("SELECT h.*, s.name AS status_name FROM histories h LEFT JOIN statuses s ON h.status_id = s.id WHERE h.complaint_id = :complaint_id ORDER BY h.created_at ASC");
  $stmt->bindParam(':complaint_id', $complaint['id']);
  $stmt->execute();
  $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Jika ada data tindakan, maka ambil lampiran foto berdasarkan id tindakan
  foreach ($histories as $key => $history) {
    $stmt = $conn->prepare("SELECT * FROM images WHERE source_type = 'history' AND source_id = :history_id");
    $stmt->bindParam(':history_id', $history['id']);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $histories[$key]['images'] = $images;
  }
} else {

  // Jika tidak ada input get, maka redirect ke halaman index
  header("Location: index.php");
  exit;
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
  <main class="container my-5">
    <h1 class="text-center display-6 mb-4">Detail Aduan</h1>

    <!-- Menampilkan Status Aduan -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="text-center mb-3">Status Aduan</h5>
        <div class="stepper-wrapper">
          <?php foreach ($statuses as $status) { ?>
            <div class="stepper-item <?php echo in_array($status['name'], array_column($histories, 'status_name')) ? 'active' : ''; ?>">
              <div class="step-counter"><?php echo ucwords($status['order']); ?></div>
              <div class="step-name"><?php echo ucwords($status['name']); ?></div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <!-- / Menampilkan Status Aduan -->

    <!-- Menampilkan Detail Aduan -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex flex-row align-items-center">
            <div class="d-block d-md-flex align-items-center">
              <h5 class="mb-0 fw-bold me-2">Kode Tiket:</h5>
              <span class="text-danger d-block d-md-inline-block fw-bold me-2"><?php echo $complaint['ticket']; ?></span>
            </div>
            <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?php echo $complaint['ticket']; ?>')">Copy</button>
          </div>
          <span class="badge rounded-pill text-bg-danger"><?php echo ucwords(end($histories)['status_name']); ?></span>
        </div>
        <hr>
        <b><?php echo date('d M Y, H:i', strtotime($complaint['created_at'])); ?> WIB</b>
        <p><?php echo htmlspecialchars($complaint['description']); ?></p>
        <b>Lokasi:</b>
        <p><?php echo htmlspecialchars($complaint['location']); ?></p>
        <?php if (!empty($complaint["images"])) { ?>
          <b>Lampiran:</b>
          <div class="row mb-3">
            <?php foreach ($complaint["images"] as $image) { ?>
              <div class="col-md-3">
                <a href="uploads/<?php echo $image['filename']; ?>" target="_blank">
                  <img src="uploads/<?php echo $image['filename']; ?>" class="img-fluid img-thumbnail" style="max-height: 200px;">
                </a>
              </div>
            <?php } ?>
          </div>
        <?php } ?>
        <b>Catatan:</b>
        <div class="accordion" id="accordionHistories">
          <?php foreach ($histories as $history) { ?>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-controls="collapseOne">
                  <span class="badge rounded-pill text-bg-danger"><?php echo ucwords($history['status_name']); ?></span>
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show">
                <div class="accordion-body">
                  <b><?php echo date('d M Y, H:i', strtotime($history['created_at'])); ?> WIB</b>
                  <p
                    class="<?php echo $history['status_name'] == 'disposisi' ? 'd-block' : 'd-none' ?> text-danger fw-bold">
                    <?php echo isset($complaint['officer']) ? 'Disposisi ke: ' . $complaint['officer'] : '' ?>
                  <p>
                  <p><?php echo htmlspecialchars($history['notes']); ?></p>

                  <!-- Menampilkan Lampiran -->
                  <?php if (!empty($history['images'])) { ?>
                    <b>Lampiran:</b>
                    <div class="row mb-3">
                      <?php foreach ($history['images'] as $image) { ?>
                        <div class="col-md-3">
                          <a href="uploads/<?php echo $image['filename']; ?>" target="_blank">
                            <img src="uploads/<?php echo $image['filename']; ?>" class="img-fluid img-thumbnail" style="max-height: 200px;">
                          </a>
                        </div>
                      <?php } ?>
                    </div>
                  <?php } ?>
                  <!-- / Menampilkan Lampiran -->
                </div>

              </div>
            </div>
          <?php } ?>
        </div>

      </div>
    </div>
    <!-- / Menampilkan Detail Aduan -->

    <!-- Menampilkan Tambah Tindakan -->
    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_id'] == $complaint['officer_id'])) { ?>
      <div class="card mt-4">
        <div class="card-header text-center">
          <h5>Tambah Tindakan</h5>
        </div>
        <div class="card-body">
          <form action="detail.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="ticket" value="<?php echo $complaint['ticket']; ?>">
            <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
            <div class="mb-3">
              <label for="action" class="form-label">Pilih Tindakan</label>
              <select class="form-select" name="status_id" required>
                <option value="" disabled selected>Pilih tindakan</option>
                <?php foreach ($statuses as $status) { ?>
                  <option value="<?php echo $status['id']; ?>" <?php echo in_array($status['name'], array_column($histories, 'status_name')) ? 'disabled' : ''; ?>><?php echo ucwords($status['name']); ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="action" class="form-label">Pilih Disposisi</label>
              <select class="form-select" name="officer_id" required <?php echo isset($complaint['officer_id']) ? 'disabled' : ''; ?>>
                <option value="" disabled selected>Pilih disposisi</option>
                <?php foreach ($officers as $officer) { ?>
                  <option
                    value="<?php echo $officer['id']; ?>"
                    <?php echo isset($complaint['officer_id']) && $complaint['officer_id'] == $officer['id'] ? 'selected' : ''; ?>>
                    <?php echo ucwords($officer['fullname']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="notes" class="form-label">Catatan</label>
              <textarea class="form-control" name="notes" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label for="file" class="form-label">Lampiran Foto</label>
              <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
            </div>
            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-plus-circle"></i> &nbsp;Simpan Tindakan</button>
          </form>
        </div>
      </div>
    <?php } ?>
    <!-- / Menampilkan Tambah Tindakan -->

  </main>
  <!-- / Menampilkan Konten -->

  <!-- Menampilkan Footer -->
  <footer class="w-100">
    <ul class="nav border-bottom pb-3 mb-3">
    </ul>
    <p class="text-center text-body-secondary mb-0 pb-3">Copyright &copy; 2025. Developed by <?php echo APP_AUTHOR; ?></p>
  </footer>
  <!-- / Menampilkan Footer -->

  <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>