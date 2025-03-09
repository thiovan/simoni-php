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

if (isset($_GET['from']) && isset($_GET['to'])) {
  $from = $_GET['from'];
  $to = $_GET['to'];

  // Buat query untuk mengambil data aduan
  $stmt = $conn->prepare("SELECT c.*, u.fullname AS officer, s.name AS status FROM complaints c LEFT JOIN users u ON c.officer_id = u.id LEFT JOIN statuses s ON c.status_id = s.id WHERE c.created_at BETWEEN :from AND :to");
  $stmt->bindParam(':from', $from);
  $stmt->bindParam(':to', $to);
  $stmt->execute();
  $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Jika tidak ada data aduan, maka redirect ke halaman kelola aduan
  header("Location: complaint.php");
  exit;
}

?>

<html>

<head>
  <title>Laporan Rekap Aduan</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    table {
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 10px;
    }
  </style>
  <script>
    window.onload = function() {
      window.print();
    }
  </script>
</head>

<body>

  <h1 style="text-align: center;">Laporan Rekap Aduan</h1>
  <h3 style="text-align: center;"><?php echo $from; ?> s/d <?php echo $to; ?></h3>

  <table style="margin: 0 auto;">
    <thead>
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Pelapor</th>
        <th>Aduan</th>
        <th>Lokasi</th>
        <th>Disposisi</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($complaints as $index => $complaint) { ?>
        <tr>
          <td><?php echo $index + 1; ?></td>
          <td><?php echo $complaint['created_at']; ?></td>
          <td><?php echo $complaint['whatsapp']; ?> (<?php echo $complaint['email']; ?>)</td>
          <td><?php echo $complaint['description']; ?></td>
          <td><?php echo $complaint['location']; ?></td>
          <td><?php echo $complaint['officer']; ?></td>
          <td><?php echo $complaint['status']; ?></td>
        </tr>
      <?php } ?>
    </tbody>
</body>

</html>