<?php
require_once __DIR__ . '/auth.php';
$currentUser = require_login();
// Admin punya halaman home sendiri
if ($currentUser['role'] === 'admin') {
    header('Location: home_admin.php');
    exit;
}
require_once __DIR__ . '/kon.php';

function beranda_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Ambil satu baris data dengan prepared statement (biar ringkas)
function beranda_row(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
{
    $statement = mysqli_prepare($conn, $sql);
    if (!$statement) {
        return null;
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($statement, $types, ...$params);
    }
    mysqli_stmt_execute($statement);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($statement)) ?: null;
    mysqli_stmt_close($statement);
    return $row;
}

function beranda_rows(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $statement = mysqli_prepare($conn, $sql);
    if (!$statement) {
        return [];
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($statement, $types, ...$params);
    }
    mysqli_stmt_execute($statement);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($statement), MYSQLI_ASSOC);
    mysqli_stmt_close($statement);
    return $rows;
}

$role = $currentUser['role'];
$userId = (int) $currentUser['id'];
$namaTampil = $currentUser['username'];
$subjudul = '';
$stats = [];
$daftar = [];

if ($role === 'siswa') {
    $siswa = beranda_row($conn, 'SELECT id, nama, nis, kelas FROM siswa WHERE users_id = ? LIMIT 1', 'i', [$userId]);
    if ($siswa) {
        $namaTampil = $siswa['nama'];
        $subjudul = 'Kelas ' . $siswa['kelas'] . ' · NIS ' . $siswa['nis'];

        $daftar = beranda_rows(
            $conn,
            'SELECT mp.nama_mapel, nr.nilai_kkm, (nr.nilai_pengetahuan + nr.nilai_keterampilan) / 2 AS rata
             FROM nilai_rapor AS nr
             INNER JOIN mapel AS mp ON mp.id = nr.mapel_id
             WHERE nr.siswa_id = ?
             ORDER BY rata DESC',
            'i',
            [(int) $siswa['id']]
        );
        $tuntas = count(array_filter($daftar, fn($n) => $n['rata'] >= $n['nilai_kkm']));
        $rataRata = $daftar ? array_sum(array_column($daftar, 'rata')) / count($daftar) : null;
        $teman = beranda_row($conn, 'SELECT COUNT(*) AS total FROM siswa WHERE kelas = ?', 's', [$siswa['kelas']]);

        $stats = [
            ['label' => 'Rata-rata Nilai', 'value' => $rataRata === null ? '—' : number_format($rataRata, 1, ',', '.')],
            ['label' => 'Mapel Dinilai', 'value' => count($daftar)],
            ['label' => 'Mapel Tuntas', 'value' => $tuntas . '/' . count($daftar)],
            ['label' => 'Teman Sekelas', 'value' => max(((int) ($teman['total'] ?? 0)) - 1, 0)],
        ];
    }
    $menu = [
        ['href' => 'nilai_siswa.php', 'title' => 'Nilai Saya', 'desc' => 'Lihat nilai pengetahuan & keterampilan setiap mapel.'],
        ['href' => 'daftarsiswa.php', 'title' => 'Teman Sekelas', 'desc' => 'Daftar siswa yang berada di kelas yang sama.'],
        ['href' => 'profile.php', 'title' => 'Profil Saya', 'desc' => 'Perbarui data diri dan kata sandi akun.'],
    ];
} else { // guru
    $guru = beranda_row($conn, 'SELECT id, nama, nip FROM guru WHERE user_id = ? LIMIT 1', 'i', [$userId]);
    if ($guru) {
        $guruId = (int) $guru['id'];
        $namaTampil = $guru['nama'];
        $subjudul = 'NIP ' . $guru['nip'];

        // Kelas + mapel yang diajar (logika sama dengan nilai.php)
        $daftar = beranda_rows(
            $conn,
            'SELECT mp.id AS mapel_id, mp.nama_mapel, s.kelas, COUNT(DISTINCT s.id) AS jumlah_siswa
             FROM mengajar AS m
             INNER JOIN mapel AS mp ON mp.id = m.mapel_id
             INNER JOIN siswa AS s ON s.id = m.siswa_id
             WHERE mp.guru_id = ?
             GROUP BY mp.id, mp.nama_mapel, s.kelas
             ORDER BY s.kelas, mp.nama_mapel',
            'i',
            [$guruId]
        );
        $mapel = beranda_row($conn, 'SELECT COUNT(*) AS total FROM mapel WHERE guru_id = ?', 'i', [$guruId]);
        $siswaDiajar = beranda_row(
            $conn,
            'SELECT COUNT(DISTINCT m.siswa_id) AS total FROM mengajar AS m INNER JOIN mapel AS mp ON mp.id = m.mapel_id WHERE mp.guru_id = ?',
            'i',
            [$guruId]
        );
        $nilai = beranda_row(
            $conn,
            'SELECT COUNT(*) AS total FROM nilai_rapor AS nr INNER JOIN mapel AS mp ON mp.id = nr.mapel_id WHERE mp.guru_id = ?',
            'i',
            [$guruId]
        );

        $stats = [
            ['label' => 'Mapel Diampu', 'value' => (int) ($mapel['total'] ?? 0)],
            ['label' => 'Kelas Diajar', 'value' => count(array_unique(array_column($daftar, 'kelas')))],
            ['label' => 'Siswa Diajar', 'value' => (int) ($siswaDiajar['total'] ?? 0)],
            ['label' => 'Nilai Terinput', 'value' => (int) ($nilai['total'] ?? 0)],
        ];
    }
    $menu = [
        ['href' => 'nilai.php', 'title' => 'Kelola Nilai', 'desc' => 'Input dan perbarui nilai siswa per kelas.'],
        ['href' => 'daftarguru.php', 'title' => 'Daftar Guru', 'desc' => 'Lihat rekan guru yang terdaftar di sistem.'],
        ['href' => 'profile.php', 'title' => 'Profil Saya', 'desc' => 'Perbarui data diri dan kata sandi akun.'],
    ];
}

// Sapaan & tanggal berbahasa Indonesia
$jam = (int) date('G');
$sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal = $hari[(int) date('w')] . ', ' . date('j') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');

// Konten "blog" sekolah — silakan ubah sesuai kebutuhan
$berita = [
    ['kategori' => 'Akademik', 'tanggal' => '18 Nov 2026', 'judul' => 'Penilaian Akhir Semester Ganjil Dimulai Bulan Depan', 'ringkas' => 'Seluruh siswa diharapkan mempersiapkan diri. Jadwal lengkap per kelas dapat dilihat melalui wali kelas masing-masing.'],
    ['kategori' => 'Prestasi', 'tanggal' => '12 Nov 2026', 'judul' => 'Tim RPL Raih Juara 2 Lomba Web Design Tingkat Provinsi', 'ringkas' => 'Selamat kepada tim siswa jurusan RPL yang telah mengharumkan nama sekolah di ajang kompetisi tahun ini.'],
    ['kategori' => 'Kegiatan', 'tanggal' => '5 Nov 2026', 'judul' => 'Class Meeting & Pentas Seni Akhir Semester', 'ringkas' => 'Rangkaian lomba antarkelas dan pentas seni akan digelar setelah penilaian akhir semester selesai.'],
];

$agenda = [
    ['tgl' => '25', 'bln' => 'Nov', 'judul' => 'Hari Guru Nasional', 'ket' => 'Upacara bersama di lapangan'],
    ['tgl' => '01', 'bln' => 'Des', 'judul' => 'Penilaian Akhir Semester', 'ket' => 'Berlangsung hingga 12 Desember'],
    ['tgl' => '19', 'bln' => 'Des', 'judul' => 'Pembagian Rapor', 'ket' => 'Diambil oleh orang tua/wali'],
];

$inisial = strtoupper(substr(trim($namaTampil), 0, 1));
$namaDepan = explode(' ', trim($namaTampil))[0];
$label = $role === 'siswa' ? 'Ruang Siswa' : ($role === 'guru' ? 'Ruang Guru' : 'Ruang Admin');
$panah = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda | SIPN</title>
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
      <!-- Kiri: label + ilustrasi (sama seperti halaman login) -->
      <div class="flex flex-col border-b border-gray-200 p-6 sm:p-8 lg:col-span-5 lg:border-b-0 lg:border-r">
        <div class="flex items-center justify-between gap-4">
          <p class="font-display text-lg font-medium"><?php echo beranda_h($label); ?></p>
          <p class="text-xs text-gray-500"><?php echo beranda_h($tanggal); ?></p>
        </div>
        <div class="mt-6 flex flex-1 items-center justify-center rounded-2xl bg-gray-100 p-8">
          <img src="ilustrasi.svg" alt="Ilustrasi" class="max-h-72 w-full object-contain">
        </div>
      </div>

      <!-- Kanan -->
      <div class="flex flex-col lg:col-span-7">
        <div class="border-b border-gray-200 p-6 sm:p-10">
          <h1 class="font-display text-4xl font-normal leading-[1.05] tracking-tight sm:text-6xl">
            <?php echo beranda_h($sapaan); ?>,<br><?php echo beranda_h($namaDepan); ?>.
          </h1>
          <div class="mt-8 flex flex-col gap-5 sm:flex-row sm:items-center">
            <a href="<?php echo beranda_h($menu[0]['href']); ?>" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-black px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-800">
              <?php echo beranda_h($menu[0]['title']); ?>
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
            <p class="max-w-sm text-sm leading-relaxed text-gray-500">
              <?php if ($role === 'siswa'): ?>
                Pantau perkembangan belajarmu, cek nilai terbaru, dan ikuti kabar terkini dari sekolah.
              <?php elseif ($role === 'guru'): ?>
                Kelola penilaian kelas Anda dengan mudah dan ikuti informasi terbaru dari sekolah.
              <?php else: ?>
                Ringkasan data Sistem Informasi Penilaian Nilai sekolah.
              <?php endif; ?>
            </p>
          </div>
        </div>

        <div class="grid flex-1 sm:grid-cols-2">
          <!-- Inisial + 2 statistik utama -->
          <div class="flex items-center gap-5 border-b border-gray-200 p-6 sm:border-b-0 sm:border-r sm:p-8">
            <div class="flex aspect-square w-28 shrink-0 items-center justify-center rounded-2xl bg-gray-100 font-display text-5xl"><?php echo beranda_h($inisial); ?></div>
            <div class="space-y-4">
              <?php foreach (array_slice($stats, 0, 2) as $stat): ?>
              <div>
                <p class="font-display text-3xl font-medium leading-none"><?php echo beranda_h($stat['value']); ?></p>
                <p class="mt-1 text-xs text-gray-500"><?php echo beranda_h($stat['label']); ?></p>
              </div>
              <?php endforeach; ?>
              <?php if (!$stats): ?><p class="text-sm text-gray-500">Profil akun belum lengkap. Hubungi administrator.</p><?php endif; ?>
            </div>
          </div>
          <!-- Info akun + link -->
          <div class="flex flex-col justify-between gap-6 p-6 sm:p-8">
            <div>
              <p class="font-medium"><?php echo beranda_h($namaTampil); ?></p>
              <p class="text-sm text-gray-500"><?php echo beranda_h($subjudul); ?></p>
              <dl class="mt-4 space-y-1.5 text-sm">
                <?php foreach (array_slice($stats, 2) as $stat): ?>
                <div class="flex justify-between gap-4"><dt class="text-gray-500"><?php echo beranda_h($stat['label']); ?></dt><dd class="font-medium"><?php echo beranda_h($stat['value']); ?></dd></div>
                <?php endforeach; ?>
              </dl>
            </div>
            <a href="<?php echo beranda_h($menu[1]['href']); ?>" class="group flex items-end justify-between font-display text-2xl font-medium leading-tight hover:text-gray-600">
              <span><?php echo beranda_h($menu[1]['title']); ?></span>
              <span class="transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5"><?php echo $panah; ?></span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== Ringkasan + Agenda ===== -->
    <section class="grid border-t border-gray-200 lg:grid-cols-12">
      <div class="border-b border-gray-200 p-6 sm:p-8 lg:col-span-8 lg:border-b-0 lg:border-r">
        <div class="mb-6 flex items-end justify-between gap-4">
          <div>
            <h2 class="font-display text-2xl font-medium">
              <?php echo $role === 'siswa' ? 'Nilai Terbaikmu' : ($role === 'guru' ? 'Kelas yang Anda Ajar' : 'Menu Utama'); ?>
            </h2>
            <p class="mt-1 text-sm text-gray-500">
              <?php echo $role === 'siswa' ? 'Mata pelajaran dengan rata-rata tertinggi.' : ($role === 'guru' ? 'Pilih kelas untuk langsung mengelola nilai.' : 'Akses cepat ke pengelolaan data.'); ?>
            </p>
          </div>
          <?php if ($role !== 'admin' && $daftar): ?>
          <a href="<?php echo beranda_h($menu[0]['href']); ?>" class="shrink-0 rounded-full border border-gray-300 px-4 py-1.5 text-sm font-medium hover:bg-gray-50">Lihat semua</a>
          <?php endif; ?>
        </div>

        <?php if ($role === 'siswa'): ?>
          <div class="divide-y divide-gray-200 border-y border-gray-200">
            <?php foreach (array_slice($daftar, 0, 5) as $i => $n): ?>
              <?php $lulus = $n['rata'] >= $n['nilai_kkm']; $persen = max(0, min(100, (float) $n['rata'])); ?>
              <div class="grid grid-cols-12 items-center gap-4 py-4">
                <span class="col-span-1 font-display text-sm text-gray-400"><?php echo sprintf('%02d', $i + 1); ?></span>
                <div class="col-span-7 min-w-0 sm:col-span-5">
                  <p class="truncate font-medium"><?php echo beranda_h($n['nama_mapel']); ?></p>
                  <p class="text-xs text-gray-500">KKM <?php echo (int) $n['nilai_kkm']; ?></p>
                </div>
                <div class="hidden h-1 rounded-full bg-gray-100 sm:col-span-3 sm:block">
                  <div class="h-1 rounded-full bg-black" style="width: <?php echo $persen; ?>%"></div>
                </div>
                <div class="col-span-4 flex items-center justify-end gap-3 sm:col-span-3">
                  <span class="rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo $lulus ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'; ?>"><?php echo $lulus ? 'Tuntas' : 'Belum'; ?></span>
                  <span class="font-display text-xl font-medium"><?php echo number_format((float) $n['rata'], 1, ',', '.'); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (!$daftar): ?>
              <p class="py-12 text-center text-sm text-gray-400">Nilai belum tersedia. Silakan cek kembali nanti.</p>
            <?php endif; ?>
          </div>
        <?php elseif ($role === 'guru'): ?>
          <div class="grid gap-3 sm:grid-cols-2">
            <?php foreach (array_slice($daftar, 0, 6) as $k): ?>
              <a href="nilai.php?mapel_id=<?php echo (int) $k['mapel_id']; ?>&amp;kelas=<?php echo rawurlencode($k['kelas']); ?>" class="group flex items-center justify-between gap-4 rounded-2xl border border-gray-200 p-5 transition hover:border-gray-900">
                <div class="min-w-0">
                  <p class="text-xs font-medium uppercase tracking-wider text-gray-500"><?php echo beranda_h($k['kelas']); ?></p>
                  <p class="mt-1 truncate font-display text-lg font-medium"><?php echo beranda_h($k['nama_mapel']); ?></p>
                  <p class="text-sm text-gray-500"><?php echo (int) $k['jumlah_siswa']; ?> siswa</p>
                </div>
                <span class="text-gray-400 transition group-hover:text-gray-900"><?php echo $panah; ?></span>
              </a>
            <?php endforeach; ?>
            <?php if (!$daftar): ?>
              <p class="rounded-2xl border border-dashed border-gray-300 py-12 text-center text-sm text-gray-400 sm:col-span-2">Belum ada kelas yang ditugaskan kepada Anda.</p>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="grid gap-3 sm:grid-cols-3">
            <?php foreach ($menu as $m): ?>
              <a href="<?php echo beranda_h($m['href']); ?>" class="group rounded-2xl border border-gray-200 p-5 transition hover:border-gray-900">
                <div class="flex items-center justify-between gap-2"><p class="font-display text-lg font-medium"><?php echo beranda_h($m['title']); ?></p><span class="text-gray-400 group-hover:text-gray-900"><?php echo $panah; ?></span></div>
                <p class="mt-1 text-sm text-gray-500"><?php echo beranda_h($m['desc']); ?></p>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <aside class="p-6 sm:p-8 lg:col-span-4">
        <h2 class="font-display text-2xl font-medium">Agenda</h2>
        <p class="mt-1 text-sm text-gray-500">Kegiatan sekolah terdekat.</p>
        <ul class="mt-6 divide-y divide-gray-200 border-y border-gray-200">
          <?php foreach ($agenda as $a): ?>
          <li class="flex items-center gap-4 py-4">
            <div class="w-12 shrink-0 text-center">
              <p class="font-display text-2xl font-medium leading-none"><?php echo beranda_h($a['tgl']); ?></p>
              <p class="mt-1 text-[11px] uppercase tracking-wider text-gray-500"><?php echo beranda_h($a['bln']); ?></p>
            </div>
            <div class="min-w-0 border-l border-gray-200 pl-4">
              <p class="truncate font-medium"><?php echo beranda_h($a['judul']); ?></p>
              <p class="truncate text-sm text-gray-500"><?php echo beranda_h($a['ket']); ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </aside>
    </section>

    <!-- ===== Kabar sekolah (gaya blog) ===== -->
    <section id="kabar" class="scroll-mt-24 border-t border-gray-200">
      <div class="flex flex-col gap-2 border-b border-gray-200 p-6 sm:flex-row sm:items-end sm:justify-between sm:p-8">
        <h2 class="font-display text-3xl font-normal tracking-tight sm:text-4xl">Kabar Sekolah</h2>
        <p class="max-w-sm text-sm text-gray-500">Informasi, prestasi, dan kegiatan terbaru dari lingkungan sekolah.</p>
      </div>
      <div class="grid divide-y divide-gray-200 md:grid-cols-3 md:divide-x md:divide-y-0">
        <?php foreach ($berita as $i => $b): ?>
        <article class="flex flex-col p-6 transition hover:bg-gray-50 sm:p-8">
          <div class="flex items-center justify-between text-xs text-gray-500">
            <span class="rounded-full border border-gray-300 px-3 py-1 font-medium text-gray-700"><?php echo beranda_h($b['kategori']); ?></span>
            <time><?php echo beranda_h($b['tanggal']); ?></time>
          </div>
          <p class="mt-8 font-display text-sm text-gray-400"><?php echo sprintf('%02d', $i + 1); ?></p>
          <h3 class="mt-2 font-display text-xl font-medium leading-snug"><?php echo beranda_h($b['judul']); ?></h3>
          <p class="mt-3 flex-1 text-sm leading-relaxed text-gray-500"><?php echo beranda_h($b['ringkas']); ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ===== Menu cepat ===== -->
    <section class="grid divide-y divide-gray-200 border-t border-gray-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
      <?php foreach ($menu as $m): ?>
      <a href="<?php echo beranda_h($m['href']); ?>" class="group flex flex-col justify-between gap-6 p-6 transition hover:bg-gray-50 sm:p-8">
        <p class="text-sm text-gray-500"><?php echo beranda_h($m['desc']); ?></p>
        <span class="flex items-end justify-between font-display text-2xl font-medium">
          <?php echo beranda_h($m['title']); ?>
          <span class="transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5"><?php echo $panah; ?></span>
        </span>
      </a>
      <?php endforeach; ?>
    </section>

    <footer class="flex flex-col items-center justify-between gap-2 border-t border-gray-200 px-6 py-5 text-sm text-gray-500 sm:flex-row sm:px-8">
      <p><span class="font-display font-medium text-gray-900">SIPN</span> — Sistem Informasi Penilaian Nilai</p>
      <p>&copy; <?php echo date('Y'); ?> XI RPL 1</p>
    </footer>
  </div>
</main>
</body>
</html>
