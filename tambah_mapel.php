<?php
require_once __DIR__ . '/auth.php';
require_admin();
include __DIR__ . '/kon.php';

// Proses simpan data mapel baru 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mapel = mysqli_real_escape_string($conn, $_POST['kode_mapel']);
    $nama_mapel = mysqli_real_escape_string($conn, $_POST['nama_mapel']);
    $guru_id    = trim($_POST['guru_id']) === '' ? 'NULL' : "'" . mysqli_real_escape_string($conn, $_POST['guru_id']) . "'";

    mysqli_query($conn, "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES ('$kode_mapel', '$nama_mapel', $guru_id)");

    echo "<script>alert('Data berhasil ditambahkan.'); window.location.href = 'daftar_mapel.php';</script>";
    exit;
}

// Daftar guru untuk pemilihan guru pengampu
$hasil_guru = mysqli_query($conn, "SELECT id, nama FROM guru ORDER BY nama");

include __DIR__ . '/navbar.php';
?>

<head>
    <title>Tambah Mata Pelajaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css">
</head>
<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 grid grid-cols-12 gap-4">

    <form class="col-span-12 md:col-span-8 md:col-start-3 bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 rounded-xl" action="tambah_mapel.php" method="POST">
      <div class="space-y-6">

        <h1 class="text-2xl font-extrabold text-gray-900">Form Tambah Mata Pelajaran</h1>

        <div>
            <label for="kode_mapel" class="block text-sm font-medium text-gray-900">Kode Mapel</label>
            <input id="kode_mapel" type="text" name="kode_mapel" autocomplete="off" placeholder="Contoh : A0007" class="mt-2 block w-full rounded-md bg-gray-50 px-3 py-1.5 text-sm text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600" required />
        </div>

        <div>
            <label for="nama_mapel" class="block text-sm font-medium text-gray-900">Nama Mapel</label>
            <input id="nama_mapel" type="text" name="nama_mapel" autocomplete="off" placeholder="Contoh : Bahasa Inggris" class="mt-2 block w-full rounded-md bg-gray-50 px-3 py-1.5 text-sm text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600" required />
        </div>

        <div>
            <label for="guru_id" class="block text-sm font-medium text-gray-900">Nama Guru <span class="text-gray-400 font-normal">(opsional)</span></label>
            <select id="guru_id" name="guru_id" class="mt-2 block w-full rounded-md bg-gray-50 px-3 py-1.5 text-sm text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600">
                <option value="">-- Belum Ditentukan --</option>
                <?php while ($row = mysqli_fetch_assoc($hasil_guru)): ?>
                    <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['nama']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

      </div>

      <div class="mt-6 flex items-center justify-end gap-x-6">
        <a href="daftar_mapel.php" class="text-sm font-semibold text-gray-900">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500">Save</button>
      </div>
    </form>
</main>
