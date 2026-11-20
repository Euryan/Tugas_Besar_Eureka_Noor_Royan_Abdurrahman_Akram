  <?php require_once __DIR__ . '/auth.php'; require_admin(); ?>
  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRdGVkppsfwNB0d2JxYA8hfHtTQQd_tcevK1KQzO6m7yQ&s" type="image/x-icon" href="/favicon.ico">
  <title>Hasilna Euy</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 antialiased">
  
  <?php include 'navbar.php'; ?>
        <div class="space-y-12">
  <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 grid grid-cols-12 gap-4">
        <form class="col-span-12 md:col-span-8 md:col-start-3 bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 rounded-xl" action="proses.php" method="POST">
      <div class="space-y-12">
                  <h1 class="text-2xl font-extrabold text-gray-900">Hasil Form Tambah Siswa Cuy >W<</h1>
<?php

    
if (isset($_POST['namalengkap'])) {
    $namalengkap = $_POST['namalengkap'];
    echo "Halo, <b>" . htmlspecialchars($namalengkap) . "</b>! Data telah diterima."."<br>";
}

if (isset($_POST['nisn'])) {$nisn = $_POST['nisn'];
    echo "Dengan NISN: " . htmlspecialchars($nisn) . "! Data telah diterima."."<br>";
}

if (isset($_POST['kelas'])) {
    $kelas = $_POST['kelas'];
    echo "Dari Kelas: " . htmlspecialchars($kelas) . "! Data telah diterima."."<br>";
}
if (isset($_POST['jeniskelamin'])) {
    $jeniskelamin = $_POST['jeniskelamin'];
    echo "Dan berjenis kelamin: " . htmlspecialchars($jeniskelamin) . "! Data telah diterima."."<br>";
}
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    echo "Dengan username: " . htmlspecialchars($username) . "! Data telah diterima."."<br>";
}
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    echo "Dengan password: " . htmlspecialchars($password) . "! Data telah diterima."."<br>" ;
}
?>
<img src="Jokowi.png" alt="Gambar" ">
</form>
</main>
