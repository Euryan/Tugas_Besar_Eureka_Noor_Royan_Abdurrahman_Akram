<?php
require_once __DIR__ . '/auth.php';
require_admin();
include __DIR__ . '/kon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = $_POST['id'];
    $guru_id    = $_POST['guru_id'];
    $kode_mapel = $_POST['kode_mapel'];
    $nama_mapel = $_POST['nama_mapel'];

    mysqli_query($conn, "UPDATE mapel SET guru_id = '$guru_id', kode_mapel = '$kode_mapel', nama_mapel = '$nama_mapel' WHERE id = '$id'");

    echo "<script>alert('Data berhasil diperbarui.'); window.location.href = 'daftar_mapel.php';</script>";
    exit;
}

$id = $_GET['id'] ?? 0;
$query = mysqli_query($conn, "SELECT mapel.*, guru.nama AS nama_guru FROM mapel LEFT JOIN guru ON mapel.guru_id = guru.id WHERE mapel.id = '$id'");
$mapel = mysqli_fetch_assoc($query);

$hasil_guru  = mysqli_query($conn, "SELECT id, nama FROM guru ORDER BY nama");

include __DIR__ . '/navbar.php';
?>

<head>
    <title>Edit Mata Pelajaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css">
</head>
<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 grid grid-cols-12 gap-4">

    <form class="col-span-12 md:col-span-8 md:col-start-3 bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 rounded-xl" action="edit_mapel.php" method="POST">
      <div class="space-y-6">

        <h1 class="text-2xl font-extrabold text-gray-900">Form Edit Mata Pelajaran</h1>

        <div>
            <label for="guru_id" class="block text-sm font-medium text-gray-900">Nama Guru</label>
            <select id="guru_id" name="guru_id" class="mt-2 block w-full rounded-md bg-gray-50 px-3 py-1.5 text-sm text-gray-900 outline-1 outline-gray-300 focus:outline-indigo-600">
                <option value="">-- Belum Ditentukan --</option>
                <?php while ($row = mysqli_fetch_assoc($hasil_guru)): ?>
                    <option value="<?= $row['id']; ?>" <?= ($mapel['guru_id'] ?? '') == $row['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($row['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="kode_mapel" class="block text-sm font-medium text-gray-900">Kode Mapel</label>
            <input id="kode_mapel" type="text" disabled value="<?= $mapel['kode_mapel'] ?? ''; ?>" class="mt-2 block w-full rounded-md bg-gray-100 px-3 py-1.5 text-sm text-gray-500 outline-1 outline-gray-300" />
        </div>

        <div>
            <label for="nama_mapel" class="block text-sm font-medium text-gray-900">Nama Mapel</label>
            <input id="nama_mapel" type="text" disabled value="<?= $mapel['nama_mapel'] ?? ''; ?>" class="mt-2 block w-full rounded-md bg-gray-100 px-3 py-1.5 text-sm text-gray-500 outline-1 outline-gray-300" />
        </div>

        <input type="hidden" name="kode_mapel" value="<?= $mapel['kode_mapel'] ?? ''; ?>">
        <input type="hidden" name="nama_mapel" value="<?= $mapel['nama_mapel'] ?? ''; ?>">
        <input type="hidden" name="id" value="<?= $mapel['id'] ?? ''; ?>">

      </div>

      <div class="mt-6 flex items-center justify-end gap-x-6">
        <a href="daftar_mapel.php" class="text-sm font-semibold text-gray-900">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500">Save</button>
      </div>
    </form>
</main>