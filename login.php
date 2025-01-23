<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
}

try {
  $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

if (isset($_POST['username']) && isset($_POST['password'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
  $stmt->bindParam(':username', $username);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    if (password_verify($password, $row['password'])) {
      session_start();
      $_SESSION['username'] = $username;
      header("Location: index.php");
      exit();
    } else {
      $_SESSION['flash_message'] = "Kata sandi salah!";
      header("Location: login.php");
      exit();
    }
  } else {
    $_SESSION['flash_message'] = "Nama pengguna tidak ditemukan!";
    header("Location: login.php");
    exit();
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
  <link rel="stylesheet" href="assets/css/custom.css">
  <script src="assets/js/bootstrap.min.js"></script>
</head>

<body class="d-flex align-items-center py-4 min-vh-100">

  <main class="form-signin w-100 m-auto">
    <form action="login/auth" method="post">
      <!-- Menampilkan Logo -->
      <img class="mb-4 mx-auto d-block" src="assets/images/logo.png" alt="" width="200">

      <!-- Menampilkan Pesan Flash -->
      <?php if (isset($_SESSION['flash_message'])) { ?>
        <div class="alert alert-danger" role="alert">
          <?php echo $_SESSION['flash_message']; ?>
          <?php unset($_SESSION['flash_message']); ?>
        </div>
      <?php } ?>

      <!-- Input Nama Pengguna -->
      <div class="form-floating">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
        <label for="floatingInput">Nama Pengguna</label>
      </div>
      <!-- Input Kata Sandi -->
      <div class="form-floating">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
        <label for="floatingPassword">Kata Sandi</label>
      </div>

      <!-- Tombol Masuk -->
      <button class="btn btn-primary w-100 py-2" type="submit">Masuk</button>

      <!-- Menampilkan Footer -->
      <p class="mt-5 mb-3 text-body-secondary text-center">Copyright &copy; 2025. Developed by <?php echo APP_AUTHOR; ?></p>
    </form>
  </main>
</body>

</html>