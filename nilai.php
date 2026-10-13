<?php
require_once __DIR__ . '/auth.php';
$currentUser = require_data_page('guru');
require_once __DIR__ . '/kon.php';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function score_class(float $score, int $kkm, bool $final = false): string
{
    $difference = $score - $kkm;
    if ($difference >= 0) {
        return $final ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800';
    }
    if ($difference >= -10) {
        return 'bg-amber-100 text-amber-800';
    }
    return 'bg-red-100 text-red-800';
}

$teacherStatement = mysqli_prepare($conn, 'SELECT id, nama FROM guru WHERE user_id = ? LIMIT 1');
mysqli_stmt_bind_param($teacherStatement, 'i', $currentUser['id']);
mysqli_stmt_execute($teacherStatement);
$teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacherStatement));
mysqli_stmt_close($teacherStatement);

if (!$teacher) {
    http_response_code(403);
    exit('Profil guru tidak ditemukan untuk akun ini.');
}

$teacherId = (int) $teacher['id'];
$subjectId = filter_var($_GET['mapel_id'] ?? null, FILTER_VALIDATE_INT);
$className = trim($_GET['kelas'] ?? '');
$isDetail = $subjectId !== false && $subjectId !== null && $subjectId > 0 && $className !== '';
$errorMessage = '';
$successMessage = $_SESSION['nilai_success'] ?? '';
$formMode = $_GET['form'] ?? '';
unset($_SESSION['nilai_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = filter_input(INPUT_POST, 'mapel_id', FILTER_VALIDATE_INT);
    $subjectId = $subjectId !== false && $subjectId !== null ? (int) $subjectId : 0;
    $className = trim($_POST['kelas'] ?? '');
    $isDetail = $subjectId > 0 && $className !== '';
    $action = $_POST['action'] ?? '';

    if (!valid_csrf_token()) {
        $errorMessage = 'Sesi formulir tidak valid. Muat ulang halaman dan coba kembali.';
    } else {
        $contextStatement = mysqli_prepare(
            $conn,
            'SELECT mp.id
             FROM mapel AS mp
             INNER JOIN guru AS g ON g.id = mp.guru_id
             WHERE mp.id = ? AND g.user_id = ?
               AND EXISTS (
                   SELECT 1 FROM mengajar AS m
                   INNER JOIN siswa AS s ON s.id = m.siswa_id
                   WHERE m.mapel_id = mp.id AND s.kelas = ?
               )
             LIMIT 1'
        );
        mysqli_stmt_bind_param($contextStatement, 'iis', $subjectId, $currentUser['id'], $className);
        mysqli_stmt_execute($contextStatement);
        $validContext = mysqli_fetch_assoc(mysqli_stmt_get_result($contextStatement)) !== null;
        mysqli_stmt_close($contextStatement);

        if (!$validContext) {
            $errorMessage = 'Kelas atau mata pelajaran tidak termasuk penugasan Anda.';
        } elseif ($action === 'delete') {
            $gradeId = filter_input(INPUT_POST, 'nilai_id', FILTER_VALIDATE_INT);
            $gradeId = $gradeId !== false && $gradeId !== null ? (int) $gradeId : 0;
            $deleteStatement = mysqli_prepare(
                $conn,
                'DELETE nr FROM nilai_rapor AS nr
                 INNER JOIN siswa AS s ON s.id = nr.siswa_id
                 WHERE nr.id = ? AND nr.mapel_id = ? AND s.kelas = ?'
            );
            mysqli_stmt_bind_param($deleteStatement, 'iis', $gradeId, $subjectId, $className);
            $deleted = mysqli_stmt_execute($deleteStatement) && mysqli_stmt_affected_rows($deleteStatement) === 1;
            mysqli_stmt_close($deleteStatement);

            if ($deleted) {
                $_SESSION['nilai_success'] = 'Data nilai berhasil dihapus.';
                header('Location: nilai.php?mapel_id=' . $subjectId . '&kelas=' . rawurlencode($className));
                exit;
            }
            $errorMessage = 'Data nilai tidak ditemukan atau gagal dihapus.';
        } elseif ($action === 'save') {
            $gradeId = filter_input(INPUT_POST, 'nilai_id', FILTER_VALIDATE_INT);
            $gradeId = $gradeId !== false && $gradeId !== null ? (int) $gradeId : 0;
            $formMode = $gradeId > 0 ? 'edit' : 'tambah';
            $studentId = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
            $studentId = $studentId !== false && $studentId !== null ? (int) $studentId : 0;
            $kkm = filter_input(INPUT_POST, 'nilai_kkm', FILTER_VALIDATE_INT);
            $knowledge = filter_input(INPUT_POST, 'nilai_pengetahuan', FILTER_VALIDATE_INT);
            $skill = filter_input(INPUT_POST, 'nilai_keterampilan', FILTER_VALIDATE_INT);
            $description = trim($_POST['keterangan'] ?? '');
            $validScores = $kkm !== false && $kkm !== null
                && $knowledge !== false && $knowledge !== null
                && $skill !== false && $skill !== null
                && $kkm >= 0 && $kkm <= 100
                && $knowledge >= 0 && $knowledge <= 100
                && $skill >= 0 && $skill <= 100
                && strlen($description) <= 50;

            if (!$validScores) {
                $errorMessage = 'KKM dan nilai harus berupa angka 0–100; keterangan maksimal 50 karakter.';
            } elseif ($gradeId > 0) {
                $existingStatement = mysqli_prepare(
                    $conn,
                    'SELECT nr.id FROM nilai_rapor AS nr
                     INNER JOIN siswa AS s ON s.id = nr.siswa_id
                     WHERE nr.id = ? AND nr.mapel_id = ? AND s.kelas = ?
                     LIMIT 1'
                );
                mysqli_stmt_bind_param($existingStatement, 'iis', $gradeId, $subjectId, $className);
                mysqli_stmt_execute($existingStatement);
                $gradeExists = mysqli_fetch_assoc(mysqli_stmt_get_result($existingStatement)) !== null;
                mysqli_stmt_close($existingStatement);

                if (!$gradeExists) {
                    $errorMessage = 'Data nilai yang akan diedit tidak ditemukan.';
                } else {
                    $updateStatement = mysqli_prepare(
                        $conn,
                        'UPDATE nilai_rapor
                         SET nilai_kkm = ?, nilai_pengetahuan = ?, nilai_keterampilan = ?, Keterangan = ?
                         WHERE id = ? AND mapel_id = ?'
                    );
                    mysqli_stmt_bind_param(
                        $updateStatement,
                        'iiisii',
                        $kkm,
                        $knowledge,
                        $skill,
                        $description,
                        $gradeId,
                        $subjectId
                    );
                    $saved = mysqli_stmt_execute($updateStatement);
                    mysqli_stmt_close($updateStatement);

                    if ($saved) {
                        $_SESSION['nilai_success'] = 'Data nilai berhasil diperbarui.';
                        header('Location: nilai.php?mapel_id=' . $subjectId . '&kelas=' . rawurlencode($className));
                        exit;
                    }
                    $errorMessage = 'Gagal memperbarui nilai.';
                }
            } else {
                $studentStatement = mysqli_prepare(
                    $conn,
                    'SELECT s.id FROM siswa AS s
                     WHERE s.id = ? AND s.kelas = ?
                     LIMIT 1'
                );
                mysqli_stmt_bind_param($studentStatement, 'is', $studentId, $className);
                mysqli_stmt_execute($studentStatement);
                $validStudent = mysqli_fetch_assoc(mysqli_stmt_get_result($studentStatement)) !== null;
                mysqli_stmt_close($studentStatement);

                if (!$validStudent) {
                    $errorMessage = 'Pilih siswa yang terdaftar pada kelas dan mata pelajaran ini.';
                } else {
                    $duplicateStatement = mysqli_prepare(
                        $conn,
                        'SELECT id FROM nilai_rapor WHERE siswa_id = ? AND mapel_id = ? LIMIT 1'
                    );
                    mysqli_stmt_bind_param($duplicateStatement, 'ii', $studentId, $subjectId);
                    mysqli_stmt_execute($duplicateStatement);
                    $duplicateExists = mysqli_fetch_assoc(mysqli_stmt_get_result($duplicateStatement)) !== null;
                    mysqli_stmt_close($duplicateStatement);

                    if ($duplicateExists) {
                        $errorMessage = 'Nilai siswa untuk mata pelajaran ini sudah tersedia. Gunakan aksi edit.';
                    } else {
                        $insertStatement = mysqli_prepare(
                            $conn,
                            'INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_kkm, nilai_pengetahuan, nilai_keterampilan, Keterangan)
                             VALUES (?, ?, ?, ?, ?, ?)'
                        );
                        mysqli_stmt_bind_param(
                            $insertStatement,
                            'iiiiis',
                            $studentId,
                            $subjectId,
                            $kkm,
                            $knowledge,
                            $skill,
                            $description
                        );
                        $saved = mysqli_stmt_execute($insertStatement);
                        $insertError = $saved ? '' : mysqli_stmt_error($insertStatement);
                        mysqli_stmt_close($insertStatement);

                        if ($saved) {
                            $_SESSION['nilai_success'] = 'Data berhasil ditambahkan.';
                            header('Location: nilai.php?mapel_id=' . $subjectId . '&kelas=' . rawurlencode($className));
                            exit;
                        }
                        $errorMessage = 'Gagal menyimpan nilai: ' . $insertError;
                    }
                }
            }
        } else {
            $errorMessage = 'Aksi tidak dikenal.';
        }
    }
}

$subjects = [];
$classStatement = mysqli_prepare(
    $conn,
    'SELECT mp.id AS mapel_id, mp.nama_mapel, mp.kode_mapel, assignments.kelas,
            COUNT(DISTINCT roster.id) AS jumlah_siswa
     FROM (
         SELECT DISTINCT m.mapel_id, assigned_students.kelas
         FROM mengajar AS m
         INNER JOIN siswa AS assigned_students ON assigned_students.id = m.siswa_id
     ) AS assignments
     INNER JOIN siswa AS roster ON roster.kelas = assignments.kelas
     INNER JOIN mapel AS mp ON mp.id = assignments.mapel_id
     WHERE mp.guru_id = ?
     GROUP BY mp.id, mp.nama_mapel, mp.kode_mapel, assignments.kelas
     ORDER BY assignments.kelas, mp.nama_mapel'
);
mysqli_stmt_bind_param($classStatement, 'i', $teacherId);
mysqli_stmt_execute($classStatement);
$classResult = mysqli_stmt_get_result($classStatement);
while ($row = mysqli_fetch_assoc($classResult)) {
    $subjects[] = $row;
}
mysqli_stmt_close($classStatement);

$subject = null;
$students = [];
$grades = [];
$editingGrade = null;
if ($isDetail) {
    $subjectStatement = mysqli_prepare(
        $conn,
        'SELECT mp.id, mp.nama_mapel, mp.kode_mapel, ? AS kelas
         FROM mapel AS mp
         WHERE mp.id = ? AND mp.guru_id = ?
           AND EXISTS (
               SELECT 1 FROM mengajar AS m
               INNER JOIN siswa AS assigned_students ON assigned_students.id = m.siswa_id
               WHERE m.mapel_id = mp.id AND assigned_students.kelas = ?
           )
         LIMIT 1'
    );
    mysqli_stmt_bind_param($subjectStatement, 'siis', $className, $subjectId, $teacherId, $className);
    mysqli_stmt_execute($subjectStatement);
    $subject = mysqli_fetch_assoc(mysqli_stmt_get_result($subjectStatement));
    mysqli_stmt_close($subjectStatement);

    if (!$subject) {
        http_response_code(404);
        $isDetail = false;
        $errorMessage = 'Penugasan kelas atau mata pelajaran tidak ditemukan.';
    } else {
        $studentStatement = mysqli_prepare(
            $conn,
            'SELECT s.id, s.nama, s.nis, nr.id AS nilai_id, nr.nilai_kkm,
                    nr.nilai_pengetahuan, nr.nilai_keterampilan, nr.Keterangan
             FROM siswa AS s
             LEFT JOIN nilai_rapor AS nr ON nr.siswa_id = s.id AND nr.mapel_id = ?
             WHERE s.kelas = ?
             ORDER BY s.nama'
        );
        mysqli_stmt_bind_param($studentStatement, 'is', $subjectId, $className);
        mysqli_stmt_execute($studentStatement);
        $studentResult = mysqli_stmt_get_result($studentStatement);
        while ($row = mysqli_fetch_assoc($studentResult)) {
            $students[] = $row;
        }
        mysqli_stmt_close($studentStatement);

        if ($formMode === 'edit') {
            $gradeId = $_SERVER['REQUEST_METHOD'] === 'POST'
                ? (int) ($_POST['nilai_id'] ?? 0)
                : filter_var($_GET['nilai_id'] ?? null, FILTER_VALIDATE_INT);
            $gradeId = $gradeId !== false && $gradeId !== null ? (int) $gradeId : 0;
            $editStatement = mysqli_prepare(
                $conn,
                'SELECT nr.id, nr.siswa_id, nr.nilai_kkm, nr.nilai_pengetahuan,
                        nr.nilai_keterampilan, nr.Keterangan, s.nama AS nama_siswa
                 FROM nilai_rapor AS nr
                 INNER JOIN siswa AS s ON s.id = nr.siswa_id
                 WHERE nr.id = ? AND nr.mapel_id = ? AND s.kelas = ?
                 LIMIT 1'
            );
            mysqli_stmt_bind_param($editStatement, 'iis', $gradeId, $subjectId, $className);
            mysqli_stmt_execute($editStatement);
            $editingGrade = mysqli_fetch_assoc(mysqli_stmt_get_result($editStatement));
            mysqli_stmt_close($editStatement);
            if (!$editingGrade) {
                $formMode = '';
                $errorMessage = 'Data nilai yang dipilih tidak ditemukan.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nilai Siswa | SIPN</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
<?php include __DIR__ . '/navbar.php'; ?>
<main class="mx-auto max-w-7xl px-4 pb-12 pt-28 sm:px-6 lg:px-8">
  <div class="mb-7">
    <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Ruang Guru</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Nilai Siswa</h1>
    <p class="mt-2 text-slate-500">Kelola nilai berdasarkan kelas dan mata pelajaran yang Anda ajar.</p>
  </div>

  <?php if ($errorMessage !== ''): ?>
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"><?php echo h($errorMessage); ?></div>
  <?php endif; ?>
  <?php if ($successMessage !== ''): ?>
    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status"><?php echo h($successMessage); ?></div>
    <script>alert(<?php echo json_encode($successMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>);</script>
  <?php endif; ?>

  <?php if ($isDetail && $subject): ?>
    <div class="mb-5 flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <a href="nilai.php" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Daftar kelas</a>
        <h2 class="mt-3 text-xl font-bold text-slate-900"><?php echo h($subject['kelas']); ?></h2>
        <p class="mt-1 text-sm text-slate-500"><?php echo h($subject['nama_mapel']); ?> <span class="text-slate-300">/</span> <?php echo h($subject['kode_mapel']); ?></p>
      </div>
      <?php if ($formMode === ''): ?>
        <a href="nilai.php?mapel_id=<?php echo (int) $subjectId; ?>&amp;kelas=<?php echo rawurlencode($className); ?>&amp;form=tambah" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">+ Tambah Nilai</a>
      <?php endif; ?>
    </div>

    <?php if ($formMode === 'tambah' || $formMode === 'edit'): ?>
      <?php
      $isEditing = $formMode === 'edit' && $editingGrade;
      $studentOptions = [];
      if (!$isEditing) {
          $optionsStatement = mysqli_prepare(
              $conn,
              'SELECT s.id, s.nama, s.nis
               FROM siswa AS s
               LEFT JOIN nilai_rapor AS nr ON nr.siswa_id = s.id AND nr.mapel_id = ?
               WHERE s.kelas = ? AND nr.id IS NULL
               ORDER BY s.nama'
          );
          mysqli_stmt_bind_param($optionsStatement, 'is', $subjectId, $className);
          mysqli_stmt_execute($optionsStatement);
          $optionsResult = mysqli_stmt_get_result($optionsStatement);
          while ($option = mysqli_fetch_assoc($optionsResult)) {
              $studentOptions[] = $option;
          }
          mysqli_stmt_close($optionsStatement);
      }
      $requestedStudentId = filter_var($_GET['siswa_id'] ?? null, FILTER_VALIDATE_INT);
      $requestedStudentId = $requestedStudentId !== false && $requestedStudentId !== null
          ? (string) $requestedStudentId
          : '';
      $formValues = $editingGrade ?: [
          'nilai_kkm' => $_POST['nilai_kkm'] ?? 75,
          'nilai_pengetahuan' => $_POST['nilai_pengetahuan'] ?? '',
          'nilai_keterampilan' => $_POST['nilai_keterampilan'] ?? '',
          'Keterangan' => $_POST['keterangan'] ?? '',
          'nama_siswa' => '',
          'siswa_id' => $_POST['siswa_id'] ?? $requestedStudentId,
      ];
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMessage !== '') {
          $formValues['nilai_kkm'] = $_POST['nilai_kkm'] ?? $formValues['nilai_kkm'];
          $formValues['nilai_pengetahuan'] = $_POST['nilai_pengetahuan'] ?? $formValues['nilai_pengetahuan'];
          $formValues['nilai_keterampilan'] = $_POST['nilai_keterampilan'] ?? $formValues['nilai_keterampilan'];
          $formValues['Keterangan'] = $_POST['keterangan'] ?? $formValues['Keterangan'];
      }
      ?>
      <form method="post" class="mb-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h3 class="mb-5 text-lg font-bold text-slate-900"><?php echo $isEditing ? 'Edit Nilai' : 'Tambah Nilai Siswa'; ?></h3>
        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="mapel_id" value="<?php echo (int) $subjectId; ?>">
        <input type="hidden" name="kelas" value="<?php echo h($className); ?>">
        <?php if ($isEditing): ?><input type="hidden" name="nilai_id" value="<?php echo (int) $editingGrade['id']; ?>"><?php endif; ?>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <label class="block text-sm font-medium text-slate-700">Mata Pelajaran
            <input value="<?php echo h($subject['nama_mapel']); ?>" readonly class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-slate-600">
          </label>
          <label class="block text-sm font-medium text-slate-700">Kelas
            <input value="<?php echo h($className); ?>" readonly class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-slate-600">
          </label>
          <?php if ($isEditing): ?>
            <label class="block text-sm font-medium text-slate-700">Nama Siswa
              <input value="<?php echo h($editingGrade['nama_siswa']); ?>" readonly class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-slate-600">
            </label>
          <?php else: ?>
            <label class="block text-sm font-medium text-slate-700">Nama Siswa
              <select name="siswa_id" required class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Pilih siswa</option>
                <?php foreach ($studentOptions as $option): ?>
                  <option value="<?php echo (int) $option['id']; ?>" <?php echo (string) $formValues['siswa_id'] === (string) $option['id'] ? 'selected' : ''; ?>><?php echo h($option['nama'] . ' (' . $option['nis'] . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          <?php endif; ?>
          <label class="block text-sm font-medium text-slate-700">Nilai KKM
            <input name="nilai_kkm" type="number" min="0" max="100" required value="<?php echo h($formValues['nilai_kkm']); ?>" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          </label>
          <label class="block text-sm font-medium text-slate-700">Nilai Pengetahuan
            <input name="nilai_pengetahuan" type="number" min="0" max="100" required value="<?php echo h($formValues['nilai_pengetahuan']); ?>" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          </label>
          <label class="block text-sm font-medium text-slate-700">Nilai Keterampilan
            <input name="nilai_keterampilan" type="number" min="0" max="100" required value="<?php echo h($formValues['nilai_keterampilan']); ?>" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          </label>
          <label class="block text-sm font-medium text-slate-700 sm:col-span-2 lg:col-span-3">Keterangan
            <input name="keterangan" maxlength="50" value="<?php echo h($formValues['Keterangan']); ?>" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          </label>
        </div>
        <?php if (!$isEditing && !$studentOptions): ?><p class="mt-4 text-sm text-amber-700">Semua siswa pada kelas ini sudah memiliki nilai untuk mata pelajaran ini.</p><?php endif; ?>
        <div class="mt-6 flex flex-wrap gap-3">
          <button type="submit" <?php echo (!$isEditing && !$studentOptions) ? 'disabled' : ''; ?> class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Simpan Nilai</button>
          <a href="nilai.php?mapel_id=<?php echo (int) $subjectId; ?>&amp;kelas=<?php echo rawurlencode($className); ?>" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
        </div>
      </form>
    <?php else: ?>
      <div class="mb-4 flex flex-wrap gap-3 rounded-xl bg-white px-4 py-3 text-xs shadow-sm ring-1 ring-slate-200">
        <span class="font-semibold text-slate-600">Indikator:</span>
        <span class="rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-800">Kompetensi ≥ KKM</span>
        <span class="rounded-full bg-amber-100 px-2.5 py-1 font-semibold text-amber-800">Di bawah KKM hingga 10 poin</span>
        <span class="rounded-full bg-red-100 px-2.5 py-1 font-semibold text-red-800">Di bawah KKM lebih dari 10 poin</span>
        <span class="rounded-full bg-emerald-100 px-2.5 py-1 font-semibold text-emerald-800">Nilai akhir ≥ KKM</span>
      </div>
      <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[950px] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>
              <th class="px-5 py-4">No</th><th class="px-5 py-4">Nama Siswa</th><th class="px-5 py-4">KKM</th><th class="px-5 py-4">Pengetahuan</th><th class="px-5 py-4">Keterampilan</th><th class="px-5 py-4">Nilai Akhir</th><th class="px-5 py-4">Keterangan</th><th class="px-5 py-4 text-center">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
              <?php foreach ($students as $index => $student): ?>
                <?php $hasGrade = $student['nilai_id'] !== null; $average = $hasGrade ? ((int) $student['nilai_pengetahuan'] + (int) $student['nilai_keterampilan']) / 2 : null; ?>
                <?php
                $rowUrl = $hasGrade
                    ? 'nilai.php?mapel_id=' . (int) $subjectId . '&kelas=' . rawurlencode($className) . '&form=edit&nilai_id=' . (int) $student['nilai_id']
                    : 'nilai.php?mapel_id=' . (int) $subjectId . '&kelas=' . rawurlencode($className) . '&form=tambah&siswa_id=' . (int) $student['id'];
                ?>
                <tr data-row-url="<?php echo h($rowUrl); ?>" tabindex="0" role="link" aria-label="<?php echo h(($hasGrade ? 'Edit nilai ' : 'Tambah nilai untuk ') . $student['nama']); ?>" class="cursor-pointer hover:bg-indigo-50/70 focus:bg-indigo-50 focus:outline-none">
                  <td class="px-5 py-4 text-slate-500"><?php echo $index + 1; ?></td>
                  <td class="px-5 py-4"><div class="font-semibold text-slate-900"><?php echo h($student['nama']); ?></div><div class="mt-0.5 text-xs text-slate-500">NIS <?php echo h($student['nis']); ?></div></td>
                  <td class="px-5 py-4"><?php echo $hasGrade ? h($student['nilai_kkm']) : '—'; ?></td>
                  <td class="px-5 py-4"><?php echo $hasGrade ? '<span class="rounded-full px-2.5 py-1 font-semibold ' . score_class((float) $student['nilai_pengetahuan'], (int) $student['nilai_kkm']) . '">' . h($student['nilai_pengetahuan']) . '</span>' : '—'; ?></td>
                  <td class="px-5 py-4"><?php echo $hasGrade ? '<span class="rounded-full px-2.5 py-1 font-semibold ' . score_class((float) $student['nilai_keterampilan'], (int) $student['nilai_kkm']) . '">' . h($student['nilai_keterampilan']) . '</span>' : '—'; ?></td>
                  <td class="px-5 py-4"><?php echo $hasGrade ? '<span class="rounded-full px-2.5 py-1 font-semibold ' . score_class($average, (int) $student['nilai_kkm'], true) . '">' . number_format($average, 1, ',', '.') . '</span>' : '—'; ?></td>
                  <td class="px-5 py-4 text-slate-600"><?php echo $hasGrade && $student['Keterangan'] !== '' ? h($student['Keterangan']) : '—'; ?></td>
                  <td class="px-5 py-4"><div class="flex justify-center gap-2">
                    <?php if ($hasGrade): ?>
                      <a href="nilai.php?mapel_id=<?php echo (int) $subjectId; ?>&amp;kelas=<?php echo rawurlencode($className); ?>&amp;form=edit&amp;nilai_id=<?php echo (int) $student['nilai_id']; ?>" class="rounded-md bg-indigo-50 px-3 py-1.5 font-semibold text-indigo-700 hover:bg-indigo-100">Edit</a>
                      <form method="post" onsubmit="return confirm('Hapus nilai siswa ini?')">
                        <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="nilai_id" value="<?php echo (int) $student['nilai_id']; ?>"><input type="hidden" name="mapel_id" value="<?php echo (int) $subjectId; ?>"><input type="hidden" name="kelas" value="<?php echo h($className); ?>">
                        <button class="rounded-md bg-red-50 px-3 py-1.5 font-semibold text-red-700 hover:bg-red-100">Hapus</button>
                      </form>
                    <?php else: ?>
                      <a href="nilai.php?mapel_id=<?php echo (int) $subjectId; ?>&amp;kelas=<?php echo rawurlencode($className); ?>&amp;form=tambah" class="rounded-md bg-indigo-50 px-3 py-1.5 font-semibold text-indigo-700 hover:bg-indigo-100">Tambah Nilai</a>
                    <?php endif; ?>
                  </div></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$students): ?><tr><td colspan="8" class="px-5 py-12 text-center text-slate-500">Belum ada siswa yang ditugaskan pada kelas ini.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-left text-sm">
          <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>
            <th class="px-5 py-4">No</th><th class="px-5 py-4">Kelas</th><th class="px-5 py-4">Mata Pelajaran</th><th class="px-5 py-4">Jumlah Siswa</th><th class="px-5 py-4 text-center">Aksi</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($subjects as $index => $assignment): ?>
              <tr class="hover:bg-slate-50">
                <td class="px-5 py-4 text-slate-500"><?php echo $index + 1; ?></td>
                <td class="px-5 py-4 font-semibold text-slate-900"><?php echo h($assignment['kelas']); ?></td>
                <td class="px-5 py-4"><span class="font-medium text-slate-900"><?php echo h($assignment['nama_mapel']); ?></span><span class="ml-2 text-xs text-slate-500"><?php echo h($assignment['kode_mapel']); ?></span></td>
                <td class="px-5 py-4"><?php echo (int) $assignment['jumlah_siswa']; ?> siswa</td>
                <td class="px-5 py-4 text-center"><a href="nilai.php?mapel_id=<?php echo (int) $assignment['mapel_id']; ?>&amp;kelas=<?php echo rawurlencode($assignment['kelas']); ?>" class="inline-flex items-center justify-center rounded-lg bg-indigo-50 px-3 py-2 font-semibold text-indigo-700 hover:bg-indigo-600 hover:text-white">Lihat</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$subjects): ?><tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Belum ada kelas yang ditugaskan. Hubungi administrator untuk mengatur penugasan mengajar.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</main>
<script>
document.querySelectorAll('tr[data-row-url]').forEach((row) => {
  const openStudentForm = () => { window.location.href = row.dataset.rowUrl; };
  row.addEventListener('click', (event) => {
    if (!event.target.closest('a, button, form, input, select, textarea, label')) {
      openStudentForm();
    }
  });
  row.addEventListener('keydown', (event) => {
    if (event.target === row && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      openStudentForm();
    }
  });
});
</script>
</body>
</html>
