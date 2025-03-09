<?php
require_once 'config.php';
session_start();

// cek jika user sudah login
if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
}

try {
  // buat koneksi ke database
  $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  // jika gagal koneksi, tampilkan error
  die("Connection failed: " . $e->getMessage());
}

// cek jika user sudah menginputkan nama pengguna dan kata sandi
if (isset($_POST['username']) && isset($_POST['password'])) {
  // ambil data yang diinputkan
  $username = $_POST['username'];
  $password = $_POST['password'];

  // buat query untuk mengambil data user berdasarkan nama pengguna yang diinputkan
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
  $stmt->bindParam(':username', $username);
  $stmt->execute();

  // cek jika data user ditemukan
  if ($stmt->rowCount() > 0) {
    // ambil data user
    $row = $stmt->fetch();

    // cek jika kata sandi yang diinputkan sama dengan yang di database
    if (password_verify($password, $row['password'])) {
      // jika sama, buat session untuk user
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['user_username'] = $row['username'];
      $_SESSION['user_fullname'] = $row['fullname'];
      $_SESSION['user_role'] = $row['role'];

      // redirect user ke halaman dashboard
      header("Location: dashboard.php");
      exit();
    }
  }

  // jika gagal, buat session untuk menampilkan pesan error
  $_SESSION['flash_message'] = "Nama Pengguna / Kata Sandi anda salah!";
  header("Location: login.php");
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
  <link rel="stylesheet" href="assets/css/custom.css">
  <script src="assets/js/bootstrap.min.js"></script>
</head>

<body class="d-flex align-items-center py-4 min-vh-100">

  <main class="form-signin w-100 m-auto">
    <!-- Menampilkan Logo dan Judul halaman -->
    <img class="mb-4 mx-auto d-block" src="assets/images/logo.png" alt="" width="300">
    <h4 class="text-center mb-2">LOGIN ADMIN</h4>
    <!-- / Menampilkan Logo dan Judul halaman -->

    <!-- Menampilkan Formulir Login -->
    <form action="login.php" method="post">

      <!-- Menampilkan Pesan Flash -->
      <?php if (isset($_SESSION['flash_message'])) { ?>
        <div class="alert alert-danger" role="alert">
          <?php echo $_SESSION['flash_message']; ?>
          <?php unset($_SESSION['flash_message']); ?>
        </div>
      <?php } ?>
      <!-- / Menampilkan Pesan Flash -->

      <div class="form-floating">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
        <label for="floatingInput">Nama Pengguna</label>
      </div>

      <div class="form-floating">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
        <label for="floatingPassword">Kata Sandi</label>
      </div>


      <button class="btn btn-danger w-100 py-2" type="submit">Masuk</button>

      <!-- Menampilkan Footer -->
      <p class="mt-5 mb-3 text-body-secondary text-center">Copyright &copy; 2025. Developed by <?php echo APP_AUTHOR; ?></p>
    </form>
    <!-- / Menampilkan Formulir Login -->

  </main>
</body>

</html>