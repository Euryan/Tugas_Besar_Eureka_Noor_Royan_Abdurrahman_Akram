<?php
require_once __DIR__ . '/auth.php';
$currentUser = require_admin_or_role('siswa');
$isAdmin = $currentUser['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Siswa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
</head>
<body class="bg-gray-50 antialiased">
<?php
include 'navbar.php';
include __DIR__ . '/kon.php';

if ($isAdmin) {
    $sql = "SELECT users.id AS user_id, users.username, siswa.nis, siswa.nama,
                   siswa.kelas, siswa.jenis_kelamin
            FROM users
            INNER JOIN siswa ON users.id = siswa.users_id
            WHERE users.role = 'siswa'
            ORDER BY users.id DESC";
    $result = mysqli_query($conn, $sql);
} else {
    $sql = "SELECT users.id AS user_id, users.username, siswa.nis, siswa.nama,
                   siswa.kelas, siswa.jenis_kelamin
            FROM users
            INNER JOIN siswa ON users.id = siswa.users_id
            WHERE users.role = 'siswa'
              AND siswa.kelas = (SELECT kelas FROM siswa WHERE users_id = ? LIMIT 1)
            ORDER BY users.id DESC";
    $statement = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($statement, 'i', $currentUser['id']);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
}
if (!$result) {
    die('Query Error: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8'));
}
?>
<main class="mx-auto max-w-7xl px-4 pt-32 pb-10 sm:px-6 lg:px-8">
  <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
    <div class="flex flex-col gap-3 border-b border-gray-900/10 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-xl font-bold text-gray-900">Data Siswa</h1>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex items-center gap-2">
          <label for="showEntries" class="whitespace-nowrap text-sm text-gray-600">Tampilkan</label>
          <select id="showEntries" class="rounded-md border border-gray-300 bg-gray-50 px-2 py-1.5 text-sm text-gray-900">
            <option value="5">5</option><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="all">Semua</option>
          </select>
          <span class="whitespace-nowrap text-sm text-gray-600">data</span>
        </div>
        <input id="searchInput" type="search" placeholder="Cari siswa..." class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-sm sm:w-56">
        <?php if ($isAdmin): ?><a href="formtambahsiswa.php" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Tambah Siswa</a><?php endif; ?>
      </div>
    </div>
    <div class="relative overflow-x-auto">
      <table class="w-full min-w-[760px] text-left text-sm text-gray-700">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
          <tr>
            <th class="px-6 py-3 font-semibold">No</th><th class="px-6 py-3 font-semibold">NIS</th><th class="px-6 py-3 font-semibold">Nama Siswa</th><th class="px-6 py-3 font-semibold">Kelas</th><th class="px-6 py-3 font-semibold">JK</th><th class="px-6 py-3 font-semibold">Username</th><?php if ($isAdmin): ?><th class="px-6 py-3 text-center font-semibold">Aksi</th><?php endif; ?>
          </tr>
        </thead>
        <tbody id="tableBody" class="divide-y divide-gray-100">
          <?php $number = 1; while ($student = mysqli_fetch_assoc($result)): ?>
          <tr class="hover:bg-indigo-50/60">
            <td class="px-6 py-4"><?php echo $number++; ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($student['nis'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($student['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($student['kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($student['jenis_kelamin'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($student['username'], ENT_QUOTES, 'UTF-8'); ?></td>
            <?php if ($isAdmin): ?><td class="px-6 py-4">
              <div class="flex items-center justify-center gap-2">
                <a href="edit.php?id=<?php echo (int) $student['user_id']; ?>&page=daftarsiswa.php" class="rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-600 hover:text-white" aria-label="Edit siswa">Edit</a>
                <a href="delete.php?id=<?php echo (int) $student['user_id']; ?>&role=siswa&page=daftarsiswa.php" onclick="return confirm('Yakin ingin menghapus data siswa ini?')" class="rounded-md bg-red-50 px-3 py-1.5 text-red-700 hover:bg-red-600 hover:text-white" aria-label="Hapus siswa">Hapus</a>
              </div>
            </td><?php endif; ?>
          </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="<?php echo $isAdmin ? '7' : '6'; ?>" class="px-6 py-8 text-center text-gray-400">Belum ada data siswa untuk kelas ini.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="flex flex-col gap-3 border-t border-gray-900/10 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
      <p id="entriesInfo" class="text-sm text-gray-500"></p><div id="paginationControls" class="flex items-center gap-1"></div>
    </div>
  </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const body = document.getElementById('tableBody');
  const rows = Array.from(body.querySelectorAll('tr')).filter(row => row.cells.length > 1);
  const pageSize = document.getElementById('showEntries');
  const search = document.getElementById('searchInput');
  const info = document.getElementById('entriesInfo');
  const pagination = document.getElementById('paginationControls');
  let page = 1;

  function render() {
    const filtered = rows.filter(row => row.textContent.toLowerCase().includes(search.value.trim().toLowerCase()));
    const perPage = pageSize.value === 'all' ? Math.max(filtered.length, 1) : Number(pageSize.value);
    const pageCount = Math.max(Math.ceil(filtered.length / perPage), 1);
    page = Math.min(page, pageCount);
    rows.forEach(row => row.hidden = true);
    filtered.slice((page - 1) * perPage, page * perPage).forEach(row => row.hidden = false);
    info.textContent = filtered.length ? `Menampilkan ${(page - 1) * perPage + 1} - ${Math.min(page * perPage, filtered.length)} dari ${filtered.length} data` : 'Tidak ada data yang cocok';
    pagination.replaceChildren();
    if (pageCount <= 1) return;
    for (let pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = String(pageNumber);
      button.className = `rounded-md border px-3 py-1.5 text-sm ${pageNumber === page ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 bg-white text-gray-700'}`;
      button.addEventListener('click', () => { page = pageNumber; render(); });
      pagination.appendChild(button);
    }
  }

  pageSize.addEventListener('change', () => { page = 1; render(); });
  search.addEventListener('input', () => { page = 1; render(); });
  render();
});
</script>
</body>
</html>