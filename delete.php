<?php
require_once 'config.php';

try {
  // Koneksi ke database
  $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  // Jika gagal, maka tampilkan pesan error
  die("Koneksi gagal: " . $e->getMessage());
}

// Cek jika ada id aduan yang dikirim
if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Buat query untuk menghapus aduan
  $stmt = $conn->prepare("DELETE FROM complaints WHERE id = :id");
  $stmt->bindParam(':id', $id);

  // Eksekusi query
  if ($stmt->execute()) {
    // Jika berhasil, redirect ke halaman dashboard
    header("Location: dashboard.php");
    exit;
  } else {
    // Jika gagal, tampilkan pesan error
    echo "Gagal menghapus aduan";
  }
} else {
  // Jika tidak ada id aduan, redirect ke halaman dashboard
  header("Location: dashboard.php");
  exit;
}
?>