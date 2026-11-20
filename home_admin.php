<?php
require_once __DIR__ . '/auth.php';
$currentUser = require_admin();
require_once __DIR__ . '/kon.php';

function admin_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Query sederhana tanpa input user, cukup mysqli_query
function admin_rows(mysqli $conn, string $sql): array
{
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

$hitung = admin_rows($conn,
    'SELECT (SELECT COUNT(*) FROM users) AS users,
            (SELECT COUNT(*) FROM siswa) AS siswa,
            (SELECT COUNT(*) FROM guru) AS guru,
            (SELECT COUNT(*) FROM mapel) AS mapel,
            (SELECT COUNT(DISTINCT kelas) FROM siswa) AS kelas,
            (SELECT COUNT(*) FROM mengajar) AS mengajar,
            (SELECT COUNT(*) FROM nilai_rapor) AS nilai,
            (SELECT AVG((nilai_pengetahuan + nilai_keterampilan) / 2) FROM nilai_rapor) AS rata'
)[0] ?? [];

// Komposisi akun per role
$perRole = ['admin' => 0, 'guru' => 0, 'siswa' => 0];
foreach (admin_rows($conn, 'SELECT role, COUNT(*) AS total FROM users GROUP BY role') as $r) {
    $perRole[$r['role']] = (int) $r['total'];
}
$totalAkun = max(array_sum($perRole), 1);

// Jumlah siswa per kelas
$perKelas = admin_rows($conn, 'SELECT kelas, COUNT(*) AS total FROM siswa GROUP BY kelas ORDER BY kelas');
$maxKelas = $perKelas ? max(array_column($perKelas, 'total')) : 1;

// Akun terbaru
$akunBaru = admin_rows($conn,
    'SELECT u.id, u.username, u.role, u.created_at, COALESCE(s.nama, g.nama) AS nama
     FROM users AS u
     LEFT JOIN siswa AS s ON s.users_id = u.id
     LEFT JOIN guru AS g ON g.user_id = u.id
     ORDER BY u.created_at DESC, u.id DESC
     LIMIT 6'
);

// Data yang perlu dicek admin
$cek = admin_rows($conn,
    "SELECT
        (SELECT COUNT(*) FROM users u WHERE u.role = 'siswa' AND NOT EXISTS (SELECT 1 FROM siswa s WHERE s.users_id = u.id))
      + (SELECT COUNT(*) FROM users u WHERE u.role = 'guru' AND NOT EXISTS (SELECT 1 FROM guru g WHERE g.user_id = u.id)) AS tanpa_profil,
        (SELECT COUNT(*) FROM mapel mp WHERE NOT EXISTS (SELECT 1 FROM guru g WHERE g.id = mp.guru_id)) AS mapel_tanpa_guru,
        (SELECT COUNT(*) FROM mapel mp WHERE NOT EXISTS (SELECT 1 FROM mengajar m WHERE m.mapel_id = mp.id)) AS mapel_belum_diajar"
)[0] ?? [];
$perhatian = [
    ['jumlah' => (int) ($cek['tanpa_profil'] ?? 0), 'teks' => 'akun guru/siswa belum punya profil (tidak bisa login)', 'href' => 'tabeluseraja.php'],
    ['jumlah' => (int) ($cek['mapel_tanpa_guru'] ?? 0), 'teks' => 'mapel belum memiliki guru pengampu', 'href' => 'daftar_mapel.php'],
    ['jumlah' => (int) ($cek['mapel_belum_diajar'] ?? 0), 'teks' => 'mapel belum dihubungkan ke siswa', 'href' => 'daftar_mengajar.php'],
];
$perhatian = array_filter($perhatian, fn($p) => $p['jumlah'] > 0);

// Sapaan & tanggal
$jam = (int) date('G');
$sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal = $hari[(int) date('w')] . ', ' . date('j') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');
$bulanPendek = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$aksi = [
    ['href' => 'Tambahuser.php', 'title' => 'Tambah User', 'desc' => 'Buat akun baru'],
    ['href' => 'formtambahsiswa.php', 'title' => 'Tambah Siswa', 'desc' => 'Daftarkan siswa'],
    ['href' => 'formtambahguru.php', 'title' => 'Tambah Guru', 'desc' => 'Daftarkan guru'],
    ['href' => 'tambah_mapel.php', 'title' => 'Tambah Mapel', 'desc' => 'Mata pelajaran baru'],
    ['href' => 'tambah_mengajar.php', 'title' => 'Tambah Mengajar', 'desc' => 'Hubungkan mapel & siswa'],
];

$panah = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Admin | SIPN</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
  <style>.font-display { font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
<?php include __DIR__ . '/navbar.php'; ?>

<main class="mx-auto max-w-7xl px-3 pb-10 pt-20 sm:px-6 lg:px-8">
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

    <!-- ===== Hero ===== -->
    <section class="grid lg:grid-cols-12">
      <div class="flex flex-col border-b border-gray-200 p-6 sm:p-8 lg:col-span-5 lg:border-b-0 lg:border-r">
        <div class="flex items-center justify-between gap-4">
          <p class="font-display text-lg font-medium">Panel Admin</p>
          <p class="text-xs text-gray-500"><?php echo admin_h($tanggal); ?></p>
        </div>
        <div class="mt-6 flex flex-1 items-center justify-center rounded-2xl bg-gray-100 p-8">
          <img src="assets/images/ilustrasi.svg" alt="Ilustrasi" class="max-h-72 w-full object-contain">
        </div>
      </div>

      <div class="flex flex-col lg:col-span-7">
        <div class="border-b border-gray-200 p-6 sm:p-10">
          <h1 class="font-display text-4xl font-normal leading-[1.05] tracking-tight sm:text-6xl">
            <?php echo admin_h($sapaan); ?>,<br><?php echo admin_h($currentUser['username']); ?>.
          </h1>
          <div class="mt-8 flex flex-col gap-5 sm:flex-row sm:items-center">
            <a href="table.php" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-black px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-800">
              Kelola Pengguna
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
            <p class="max-w-sm text-sm leading-relaxed text-gray-500">Pantau seluruh data sekolah—akun, siswa, guru, mapel, dan nilai—dari satu tempat.</p>
          </div>
        </div>

        <div class="grid flex-1 sm:grid-cols-2">
          <!-- Siswa & guru -->
          <div class="flex items-center gap-5 border-b border-gray-200 p-6 sm:border-b-0 sm:border-r sm:p-8">
            <div class="flex aspect-square w-28 shrink-0 flex-col items-center justify-center rounded-2xl bg-black text-white">
              <span class="font-display text-4xl font-medium leading-none"><?php echo (int) ($hitung['users'] ?? 0); ?></span>
              <span class="mt-1 text-xs text-gray-300">Total akun</span>
            </div>
            <div class="space-y-4">
              <div>
                <p class="font-display text-3xl font-medium leading-none"><?php echo (int) ($hitung['siswa'] ?? 0); ?></p>
                <p class="mt-1 text-xs text-gray-500">Siswa terdaftar</p>
              </div>
              <div>
                <p class="font-display text-3xl font-medium leading-none"><?php echo (int) ($hitung['guru'] ?? 0); ?></p>
                <p class="mt-1 text-xs text-gray-500">Guru terdaftar</p>
              </div>
            </div>
          </div>
          <!-- Komposisi akun -->
          <div class="flex flex-col justify-between gap-6 p-6 sm:p-8">
            <div>
              <p class="font-medium">Komposisi Akun</p>
              <div class="mt-3 flex h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="bg-black" style="width: <?php echo $perRole['admin'] / $totalAkun * 100; ?>%"></div>
                <div class="bg-gray-500" style="width: <?php echo $perRole['guru'] / $totalAkun * 100; ?>%"></div>
                <div class="bg-gray-300" style="width: <?php echo $perRole['siswa'] / $totalAkun * 100; ?>%"></div>
              </div>
              <dl class="mt-4 space-y-1.5 text-sm">
                <?php foreach (['admin' => 'bg-black', 'guru' => 'bg-gray-500', 'siswa' => 'bg-gray-300'] as $r => $warna): ?>
                <div class="flex items-center justify-between gap-4">
                  <dt class="flex items-center gap-2 capitalize text-gray-500"><span class="h-2 w-2 rounded-full <?php echo $warna; ?>"></span><?php echo $r; ?></dt>
                  <dd class="font-medium"><?php echo $perRole[$r]; ?></dd>
                </div>
                <?php endforeach; ?>
              </dl>
            </div>
            <a href="Tambahuser.php" class="group flex items-end justify-between font-display text-2xl font-medium leading-tight hover:text-gray-600">
              <span>Tambah User</span>
              <span class="transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5"><?php echo $panah; ?></span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== Statistik akademik ===== -->
    <section class="grid grid-cols-2 border-t border-gray-200 lg:grid-cols-4">
      <?php
      $statAkademik = [
          ['label' => 'Mata Pelajaran', 'value' => (int) ($hitung['mapel'] ?? 0), 'href' => 'daftar_mapel.php'],
          ['label' => 'Kelas Aktif', 'value' => (int) ($hitung['kelas'] ?? 0), 'href' => 'daftarsiswa.php'],
          ['label' => 'Data Mengajar', 'value' => (int) ($hitung['mengajar'] ?? 0), 'href' => 'daftar_mengajar.php'],
          ['label' => 'Rata-rata Nilai', 'value' => ($hitung['rata'] ?? null) === null ? '—' : number_format((float) $hitung['rata'], 1, ',', '.'), 'href' => null, 'sub' => (int) ($hitung['nilai'] ?? 0) . ' nilai terinput'],
      ];
      foreach ($statAkademik as $i => $s):
          $garis = ($i % 2 === 0 ? 'border-r ' : '') . ($i < 2 ? 'border-b lg:border-b-0 ' : '') . ($i === 1 ? 'lg:border-r' : '');
      ?>
      <div class="border-gray-200 p-6 sm:p-8 <?php echo $garis; ?>">
        <p class="text-sm text-gray-500"><?php echo admin_h($s['label']); ?></p>
        <p class="mt-3 font-display text-4xl font-medium leading-none"><?php echo admin_h($s['value']); ?></p>
        <?php if (!empty($s['href'])): ?>
          <a href="<?php echo admin_h($s['href']); ?>" class="mt-3 inline-block text-xs font-medium text-gray-500 underline-offset-4 hover:text-gray-900 hover:underline">Lihat data &rarr;</a>
        <?php else: ?>
          <p class="mt-3 text-xs text-gray-500"><?php echo admin_h($s['sub']); ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </section>

    <!-- ===== Akun terbaru + sisi kanan ===== -->
    <section class="grid border-t border-gray-200 lg:grid-cols-12">
      <div class="border-b border-gray-200 p-6 sm:p-8 lg:col-span-8 lg:border-b-0 lg:border-r">
        <div class="mb-6 flex items-end justify-between gap-4">
          <div>
            <h2 class="font-display text-2xl font-medium">Akun Terbaru</h2>
            <p class="mt-1 text-sm text-gray-500">Pengguna yang terakhir ditambahkan ke sistem.</p>
          </div>
          <a href="tabeluseraja.php" class="shrink-0 rounded-full border border-gray-300 px-4 py-1.5 text-sm font-medium hover:bg-gray-50">Lihat semua</a>
        </div>
        <div class="divide-y divide-gray-200 border-y border-gray-200">
          <?php foreach ($akunBaru as $u): ?>
            <?php $tgl = strtotime($u['created_at']); ?>
            <div class="flex items-center gap-4 py-3.5">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 font-display font-medium"><?php echo admin_h(strtoupper(substr($u['username'], 0, 1))); ?></span>
              <div class="min-w-0 flex-1">
                <p class="truncate font-medium"><?php echo admin_h($u['nama'] ?? $u['username']); ?></p>
                <p class="truncate text-xs text-gray-500">@<?php echo admin_h($u['username']); ?><?php if ($tgl): ?> · <?php echo date('j', $tgl) . ' ' . $bulanPendek[(int) date('n', $tgl)] . ' ' . date('Y', $tgl); ?><?php endif; ?></p>
              </div>
              <span class="hidden rounded-full border border-gray-300 px-2.5 py-0.5 text-xs font-medium capitalize text-gray-700 sm:inline"><?php echo admin_h($u['role']); ?></span>
              <a href="edit.php?id=<?php echo (int) $u['id']; ?>&amp;page=tabeluseraja.php" class="rounded-full px-3 py-1.5 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900">Edit</a>
            </div>
          <?php endforeach; ?>
          <?php if (!$akunBaru): ?>
            <p class="py-12 text-center text-sm text-gray-400">Belum ada akun.</p>
          <?php endif; ?>
        </div>
      </div>

      <aside class="divide-y divide-gray-200 lg:col-span-4">
        <!-- Perlu perhatian -->
        <div class="p-6 sm:p-8">
          <h2 class="font-display text-2xl font-medium">Perlu Perhatian</h2>
          <?php if ($perhatian): ?>
            <ul class="mt-4 space-y-2">
              <?php foreach ($perhatian as $p): ?>
              <li>
                <a href="<?php echo admin_h($p['href']); ?>" class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 hover:border-amber-300">
                  <span class="font-display text-lg font-medium leading-none"><?php echo $p['jumlah']; ?></span>
                  <span><?php echo admin_h($p['teks']); ?></span>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="mt-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
              Semua data dalam kondisi baik.
            </p>
          <?php endif; ?>
        </div>

        <!-- Siswa per kelas -->
        <div class="p-6 sm:p-8">
          <h2 class="font-display text-2xl font-medium">Siswa per Kelas</h2>
          <ul class="mt-4 space-y-3">
            <?php foreach ($perKelas as $k): ?>
            <li>
              <div class="flex justify-between text-sm"><span class="font-medium"><?php echo admin_h($k['kelas']); ?></span><span class="text-gray-500"><?php echo (int) $k['total']; ?> siswa</span></div>
              <div class="mt-1.5 h-1 rounded-full bg-gray-100"><div class="h-1 rounded-full bg-black" style="width: <?php echo $k['total'] / $maxKelas * 100; ?>%"></div></div>
            </li>
            <?php endforeach; ?>
            <?php if (!$perKelas): ?><li class="text-sm text-gray-400">Belum ada data siswa.</li><?php endif; ?>
          </ul>
        </div>
      </aside>
    </section>

    <!-- ===== Aksi cepat ===== -->
    <section class="border-t border-gray-200">
      <div class="border-b border-gray-200 px-6 py-5 sm:px-8">
        <h2 class="font-display text-2xl font-medium">Aksi Cepat</h2>
      </div>
      <div class="grid divide-y divide-gray-200 sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-5 lg:divide-x">
        <?php foreach ($aksi as $a): ?>
        <a href="<?php echo admin_h($a['href']); ?>" class="group flex flex-col justify-between gap-6 p-6 transition hover:bg-gray-50 sm:border-b sm:border-gray-200 lg:border-b-0">
          <p class="text-sm text-gray-500"><?php echo admin_h($a['desc']); ?></p>
          <span class="flex items-end justify-between gap-2 font-display text-xl font-medium">
            <?php echo admin_h($a['title']); ?>
            <span class="transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5"><?php echo $panah; ?></span>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
    </section>

    <footer class="flex flex-col items-center justify-between gap-2 border-t border-gray-200 px-6 py-5 text-sm text-gray-500 sm:flex-row sm:px-8">
      <p><span class="font-display font-medium text-gray-900">SIPN</span> — Sistem Informasi Penilaian Nilai</p>
      <p>&copy; <?php echo date('Y'); ?> XI RPL 1</p>
    </footer>
  </div>
</main>
</body>
</html>
