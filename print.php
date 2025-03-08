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

  // Buat query untuk mengambil data aduan
  $stmt = $conn->prepare("SELECT * FROM complaints WHERE id = :id");
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

  // Cek jika aduan ada
  if ($complaint) {
    // Buat query untuk mengambil data riwayat aduan
    $stmt = $conn->prepare("SELECT * FROM histories WHERE complaint_id = :complaint_id ORDER BY created_at ASC");
    $stmt->bindParam(':complaint_id', $id);
    $stmt->execute();
    $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buat query untuk mengambil data lampiran foto
    $stmt = $conn->prepare("SELECT * FROM images WHERE history_id = :history_id");
    $stmt->bindParam(':history_id', $histories[0]['id']);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tampilkan rincian aduan
    ?>
    <html>
      <head>
        <title>Rincian Aduan</title>
        <style>
          body {
            font-family: Arial, sans-serif;
          }
          table {
            border-collapse: collapse;
          }
          th, td {
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
        <h1>Rincian Aduan</h1>
        <table>
          <tr>
            <th style="text-align: left;">No. Tiket</th>
            <td><?php echo $complaint['ticket']; ?></td>
          </tr>
          <tr>
            <th style="text-align: left;">Tanggal</th>
            <td><?php echo date('d M Y, H:i', strtotime($complaint['created_at'])); ?></td>
          </tr>
          <tr>
            <th style="text-align: left;">Pelapor</th>
            <td><?php echo $complaint['whatsapp']; ?> (<?php echo $complaint['email']; ?>)</td>
          </tr>
          <tr>
            <th style="text-align: left;">Deskripsi</th>
            <td><?php echo $complaint['description']; ?></td>
          </tr>
          <tr>
            <th style="text-align: left;">Lokasi</th>
            <td><?php echo $complaint['location']; ?></td>
          </tr>
          <tr>
            <th style="text-align: left;">Progress Aduan</th>
            <td>
              <ul>
                <?php foreach ($histories as $index => $history) { ?>
                  <li><?php echo empty($history['notes']) && $index === 0 ? "Aduan masuk." : $history['notes']; ?> (<?php echo date('d M Y, H:i', strtotime($history['created_at'])); ?>)</li>
                <?php } ?>
              </ul>
            </td>
          </tr>
          <tr>
            <th style="text-align: left;">Lampiran Foto</th>
            <td>
              <?php foreach ($images as $image) { ?>
                <img src="/simoni/uploads/<?php echo $image['filename']; ?>" width="100" height="100">
              <?php } ?>
            </td>
          </tr>
        </table>
      </body>
    </html>
    <?php
  } else {
    echo "Aduan tidak ditemukan";
  }
} else {
  echo "Id aduan tidak dikirim";
}
?>
