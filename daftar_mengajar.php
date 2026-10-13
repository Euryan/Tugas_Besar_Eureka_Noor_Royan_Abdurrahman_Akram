<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Mata Pelajaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>

</head>
<body class="bg-gray-50 antialiased">
<?php
require_once __DIR__ . '/auth.php';
require_admin();
include __DIR__ . "/kon.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $className = trim($_POST['kelas'] ?? '');
    $subjectId = filter_input(INPUT_POST, 'mapel_id', FILTER_VALIDATE_INT) ?: 0;
    $statement = mysqli_prepare($conn, 'DELETE m FROM mengajar AS m INNER JOIN siswa AS s ON s.id = m.siswa_id WHERE s.kelas = ? AND m.mapel_id = ?');

    if ($className !== '' && $subjectId > 0 && $statement && mysqli_stmt_bind_param($statement, 'si', $className, $subjectId) && mysqli_stmt_execute($statement)) {
        mysqli_stmt_close($statement);
        echo "<script>alert('Data mengajar berhasil dihapus.'); window.location.href = 'daftar_mengajar.php';</script>";
        exit;
    }

    $error = $statement ? mysqli_stmt_error($statement) : mysqli_error($conn);
    if ($statement) {
        mysqli_stmt_close($statement);
    }
    echo "<script>alert(" . json_encode('Gagal menghapus data mengajar: ' . $error) . "); window.location.href = 'daftar_mengajar.php';</script>";
    exit;
}

$sql = "SELECT s.kelas, g.nama AS nama_guru, mp.nama_mapel, mp.kode_mapel,
               mp.id AS mapel_id, COUNT(DISTINCT s.id) AS jumlah_siswa
        FROM mengajar AS m
        INNER JOIN siswa AS s ON s.id = m.siswa_id
        INNER JOIN mapel AS mp ON mp.id = m.mapel_id
        INNER JOIN guru AS g ON g.id = mp.guru_id
        GROUP BY s.kelas, g.nama, mp.nama_mapel, mp.kode_mapel, mp.id
        ORDER BY s.kelas, g.nama, mp.nama_mapel";
$hasil = mysqli_query($conn, $sql);
if (!$hasil) {
    die('Query Error: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8'));
}

include "navbar.php";
?>
<main class="mx-auto max-w-7xl px-4 pt-32 pb-10 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl">
        <div class="flex flex-col gap-3 px-6 py-5 border-b border-gray-900/10 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-xl font-bold text-gray-900">Daftar Mengajar</h1>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex items-center gap-2">
                    <label for="showEntries" class="text-sm text-gray-600 whitespace-nowrap">Tampilkan</label>
                    <select id="showEntries" class="rounded-md bg-gray-50 border border-gray-300 text-sm text-gray-900 px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-600">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">Semua</option>
                    </select>
                    <span class="text-sm text-gray-600 whitespace-nowrap">data</span>
                </div>
                <div class="relative">
                    <svg class="absolute left-2.5 top-2.5 size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m0 0a7.5 7.5 0 1 0-10.6-10.6 7.5 7.5 0 0 0 10.6 10.6Z" /></svg>
                    <input id="searchInput" type="text" placeholder="Cari guru, kelas, atau mapel..." class="w-full sm:w-64 rounded-md bg-gray-50 border border-gray-300 pl-8 pr-3 py-1.5 text-sm text-gray-900 outline-none focus:ring-2 focus:ring-indigo-600">
                </div>
                <a href="tambah_mengajar.php" title="Tambah Mengajar" aria-label="Tambah Mengajar"
                   class="inline-flex items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Mengajar
                </a>
            </div>
        </div>

        <div class="relative overflow-x-auto">
            <table class="w-full min-w-[900px] table-fixed text-sm text-left text-gray-700">
                <colgroup>
                    <col class="w-[8%]">
                    <col class="w-[22%]">
                    <col class="w-[14%]">
                    <col class="w-[22%]">
                    <col class="w-[16%]">
                    <col class="w-[18%]">
                </colgroup>
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">No</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Nama Guru</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Kelas</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Nama Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Kode Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-100">
                    <?php $no = 1; while ($mapel = mysqli_fetch_assoc($hasil)): ?>
                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-indigo-50/60 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo $no++; ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($mapel['nama_guru'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($mapel['kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($mapel['nama_mapel'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($mapel['kode_mapel'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="tambah_mengajar.php?kelas=<?php echo rawurlencode($mapel['kelas']); ?>&mapel_id=<?php echo (int) $mapel['mapel_id']; ?>" title="Edit" aria-label="Edit"
                                   class="inline-flex items-center justify-center size-8 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form method="POST" onsubmit="return confirm('Hapus penugasan mapel ini untuk seluruh siswa di kelas tersebut?')">
                                   <input type="hidden" name="action" value="delete">
                                   <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($mapel['kelas'], ENT_QUOTES, 'UTF-8'); ?>">
                                   <input type="hidden" name="mapel_id" value="<?php echo (int) $mapel['mapel_id']; ?>">
                                   <button type="submit" title="Hapus" aria-label="Hapus" class="inline-flex items-center justify-center size-8 rounded-md bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                   </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (mysqli_num_rows($hasil) === 0): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data mengajar.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 px-6 py-4 border-t border-gray-900/10 sm:flex-row sm:items-center sm:justify-between">
            <p id="entriesInfo" class="text-sm text-gray-500"></p>
            <div id="paginationControls" class="flex items-center gap-1"></div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('tableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr')).filter(r => r.children.length > 1);
    const showEntries = document.getElementById('showEntries');
    const searchInput = document.getElementById('searchInput');
    const entriesInfo = document.getElementById('entriesInfo');
    const paginationControls = document.getElementById('paginationControls');
    let currentPage = 1;

    function getFilteredRows() {
        const keyword = searchInput.value.trim().toLowerCase();
        return rows.filter(r => r.textContent.toLowerCase().includes(keyword));
    }

    function render() {
        const filtered = getFilteredRows();
        const perPage = showEntries.value === 'all' ? filtered.length || 1 : parseInt(showEntries.value, 10);
        const totalPages = Math.max(Math.ceil(filtered.length / perPage), 1);
        currentPage = Math.min(currentPage, totalPages);

        rows.forEach(r => r.style.display = 'none');

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(r => r.style.display = '');

        entriesInfo.textContent = filtered.length === 0
            ? 'Tidak ada data yang cocok'
            : `Menampilkan ${start + 1} - ${Math.min(end, filtered.length)} dari ${filtered.length} data`;

        paginationControls.innerHTML = '';
        if (totalPages <= 1) return;

        const makeBtn = (label, page, disabled = false, active = false) => {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.disabled = disabled;
            btn.className = `px-3 py-1.5 text-sm rounded-md border ${active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'} ${disabled ? 'opacity-40 cursor-not-allowed' : ''}`;
            btn.addEventListener('click', () => { currentPage = page; render(); });
            return btn;
        };

        paginationControls.appendChild(makeBtn('Prev', currentPage - 1, currentPage === 1));
        for (let p = 1; p <= totalPages; p++) {
            paginationControls.appendChild(makeBtn(String(p), p, false, p === currentPage));
        }
        paginationControls.appendChild(makeBtn('Next', currentPage + 1, currentPage === totalPages));
    }

    showEntries.addEventListener('change', () => { currentPage = 1; render(); });
    searchInput.addEventListener('input', () => { currentPage = 1; render(); });
    render();
});
</script>
</body>
</html>
