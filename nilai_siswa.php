<?php
require_once __DIR__ . '/auth.php';
$currentUser = require_data_page('siswa');
require_once __DIR__ . '/kon.php';

function nilai_siswa_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$profileStatement = mysqli_prepare(
    $conn,
    'SELECT id, nama, nis, kelas FROM siswa WHERE users_id = ? LIMIT 1'
);
mysqli_stmt_bind_param($profileStatement, 'i', $currentUser['id']);
mysqli_stmt_execute($profileStatement);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($profileStatement));
mysqli_stmt_close($profileStatement);

if (!$student) {
    http_response_code(403);
    exit('Profil siswa tidak ditemukan untuk akun ini.');
}

$gradeStatement = mysqli_prepare(
    $conn,
    'SELECT mp.nama_mapel, nr.nilai_kkm, nr.nilai_pengetahuan,
            nr.nilai_keterampilan, nr.Keterangan
     FROM nilai_rapor AS nr
     INNER JOIN mapel AS mp ON mp.id = nr.mapel_id
     WHERE nr.siswa_id = ?
     ORDER BY mp.nama_mapel'
);
mysqli_stmt_bind_param($gradeStatement, 'i', $student['id']);
mysqli_stmt_execute($gradeStatement);
$gradeResult = mysqli_stmt_get_result($gradeStatement);
$grades = [];
$averageTotal = 0;
while ($grade = mysqli_fetch_assoc($gradeResult)) {
    $grade['nilai_rata_rata'] = ((int) $grade['nilai_pengetahuan'] + (int) $grade['nilai_keterampilan']) / 2;
    $averageTotal += $grade['nilai_rata_rata'];
    $grades[] = $grade;
}
mysqli_stmt_close($gradeStatement);
$overallAverage = count($grades) > 0 ? $averageTotal / count($grades) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nilai Saya | SIPN</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
<?php include __DIR__ . '/navbar.php'; ?>
<main class="mx-auto max-w-7xl px-4 pb-12 pt-28 sm:px-6 lg:px-8">
  <div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Akademik</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Nilai Saya</h1>
    <p class="mt-2 text-slate-500">Ringkasan hasil belajar dan nilai setiap mata pelajaran.</p>
  </div>

  <section class="mb-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:col-span-2">
      <p class="text-sm font-medium text-slate-500">Nama Siswa</p>
      <h2 class="mt-2 text-2xl font-bold text-slate-900"><?php echo nilai_siswa_h($student['nama']); ?></h2>
      <p class="mt-2 text-sm text-slate-500">NIS <?php echo nilai_siswa_h($student['nis']); ?></p>
    </div>
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
      <p class="text-sm font-medium text-slate-500">Kelas</p>
      <p class="mt-2 text-xl font-bold text-slate-900"><?php echo nilai_siswa_h($student['kelas']); ?></p>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 p-5 text-white shadow-lg shadow-indigo-200">
      <p class="text-sm font-medium text-indigo-100">Rata-Rata Keseluruhan</p>
      <p class="mt-2 text-3xl font-bold"><?php echo $overallAverage === null ? '—' : number_format($overallAverage, 1, ',', '.'); ?></p>
      <p class="mt-1 text-xs text-indigo-100"><?php echo count($grades); ?> mata pelajaran dinilai</p>
    </div>
  </section>

  <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
      <h2 class="font-bold text-slate-900">Daftar Nilai Mata Pelajaran</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[850px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>
          <th class="px-5 py-4">No</th><th class="px-5 py-4">Mata Pelajaran</th><th class="px-5 py-4">KKM</th><th class="px-5 py-4">Pengetahuan</th><th class="px-5 py-4">Keterampilan</th><th class="px-5 py-4">Rata-Rata</th><th class="px-5 py-4">Keterangan</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($grades as $index => $grade): ?>
            <?php $averageClass = $grade['nilai_rata_rata'] >= (int) $grade['nilai_kkm'] ? 'bg-emerald-100 text-emerald-800' : (((int) $grade['nilai_kkm'] - $grade['nilai_rata_rata']) <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'); ?>
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-4 text-slate-500"><?php echo $index + 1; ?></td>
              <td class="px-5 py-4 font-semibold text-slate-900"><?php echo nilai_siswa_h($grade['nama_mapel']); ?></td>
              <td class="px-5 py-4"><?php echo nilai_siswa_h($grade['nilai_kkm']); ?></td>
              <td class="px-5 py-4"><?php echo nilai_siswa_h($grade['nilai_pengetahuan']); ?></td>
              <td class="px-5 py-4"><?php echo nilai_siswa_h($grade['nilai_keterampilan']); ?></td>
              <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 font-semibold <?php echo $averageClass; ?>"><?php echo number_format($grade['nilai_rata_rata'], 1, ',', '.'); ?></span></td>
              <td class="px-5 py-4 text-slate-600"><?php echo $grade['Keterangan'] !== '' ? nilai_siswa_h($grade['Keterangan']) : '—'; ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$grades): ?><tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Nilai belum tersedia. Silakan cek kembali nanti.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
