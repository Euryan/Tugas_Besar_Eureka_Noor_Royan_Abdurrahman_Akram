<?php
require_once __DIR__ . '/auth.php';
require_admin();
include __DIR__ . '/kon.php';

$isEdit = isset($_GET['kelas'], $_GET['mapel_id']);
$oldClass = $isEdit ? trim($_GET['kelas']) : '';
$oldSubjectId = $isEdit ? (int) $_GET['mapel_id'] : 0;
$selectedClass = $oldClass;
$selectedSubjectId = $oldSubjectId;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedClass = trim($_POST['kelas'] ?? '');
    $selectedSubjectId = filter_input(INPUT_POST, 'mapel_id', FILTER_VALIDATE_INT) ?: 0;
    $isEdit = ($_POST['is_edit'] ?? '') === '1';
    $oldClass = trim($_POST['old_kelas'] ?? '');
    $oldSubjectId = (int) ($_POST['old_mapel_id'] ?? 0);

    $classStatement = mysqli_prepare($conn, 'SELECT id FROM siswa WHERE kelas = ?');
    mysqli_stmt_bind_param($classStatement, 's', $selectedClass);
    mysqli_stmt_execute($classStatement);
    $classResult = mysqli_stmt_get_result($classStatement);
    $studentIds = [];
    while ($student = mysqli_fetch_assoc($classResult)) {
        $studentIds[] = (int) $student['id'];
    }
    mysqli_stmt_close($classStatement);

    $subjectStatement = mysqli_prepare($conn, 'SELECT id FROM mapel WHERE id = ? AND guru_id IS NOT NULL');
    mysqli_stmt_bind_param($subjectStatement, 'i', $selectedSubjectId);
    mysqli_stmt_execute($subjectStatement);
    $subjectExists = mysqli_stmt_get_result($subjectStatement);
    $validSubject = mysqli_num_rows($subjectExists) === 1;
    mysqli_stmt_close($subjectStatement);

    if ($selectedClass === '' || !$studentIds || !$validSubject) {
        $errorMessage = 'Pilih kelas yang memiliki siswa dan pasangan guru-mapel yang valid.';
    } else {
        $conflictSql = 'SELECT COUNT(*) AS total FROM mengajar AS m INNER JOIN siswa AS s ON s.id = m.siswa_id WHERE s.kelas = ? AND m.mapel_id <> ?';
        if ($isEdit) {
            $conflictSql .= ' AND NOT (s.kelas = ? AND m.mapel_id = ?)';
        }
        $conflictStatement = mysqli_prepare($conn, $conflictSql);
        if ($isEdit) {
            mysqli_stmt_bind_param($conflictStatement, 'sisi', $selectedClass, $selectedSubjectId, $oldClass, $oldSubjectId);
        } else {
            mysqli_stmt_bind_param($conflictStatement, 'si', $selectedClass, $selectedSubjectId);
        }
        mysqli_stmt_execute($conflictStatement);
        $conflictResult = mysqli_stmt_get_result($conflictStatement);
        $conflictCount = (int) mysqli_fetch_assoc($conflictResult)['total'];
        mysqli_stmt_close($conflictStatement);

        if ($conflictCount > 0) {
            $errorMessage = 'Kelas ini sudah memiliki penugasan mapel lain. Struktur database saat ini hanya mengizinkan satu mapel per siswa.';
        } else {
            mysqli_begin_transaction($conn);
            $saved = true;

            if ($isEdit) {
                $deleteOld = mysqli_prepare($conn, 'DELETE m FROM mengajar AS m INNER JOIN siswa AS s ON s.id = m.siswa_id WHERE s.kelas = ? AND m.mapel_id = ?');
                $saved = $deleteOld && mysqli_stmt_bind_param($deleteOld, 'si', $oldClass, $oldSubjectId) && mysqli_stmt_execute($deleteOld);
                if ($deleteOld) {
                    mysqli_stmt_close($deleteOld);
                }
            }

            if ($saved) {
                $insert = mysqli_prepare($conn, 'INSERT INTO mengajar (mapel_id, siswa_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE mapel_id = VALUES(mapel_id)');
                if (!$insert) {
                    $saved = false;
                } else {
                    foreach ($studentIds as $studentId) {
                        if (!mysqli_stmt_bind_param($insert, 'ii', $selectedSubjectId, $studentId) || !mysqli_stmt_execute($insert)) {
                            $saved = false;
                            break;
                        }
                    }
                    mysqli_stmt_close($insert);
                }
            }

            if ($saved) {
                mysqli_commit($conn);
                echo "<script>alert('Data mengajar berhasil " . ($isEdit ? 'diperbarui' : 'ditambahkan') . ".'); window.location.href = 'daftar_mengajar.php';</script>";
                exit;
            }

            $errorMessage = 'Gagal menyimpan data mengajar: ' . mysqli_error($conn);
            mysqli_rollback($conn);
        }
    }
}

$classResult = mysqli_query($conn, 'SELECT DISTINCT kelas FROM siswa ORDER BY kelas');
$subjectResult = mysqli_query($conn, 'SELECT mapel.id, mapel.nama_mapel, mapel.kode_mapel, guru.nama AS nama_guru FROM mapel INNER JOIN guru ON guru.id = mapel.guru_id ORDER BY guru.nama, mapel.nama_mapel');
$editingAssignmentExists = false;
if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $check = mysqli_prepare($conn, 'SELECT 1 FROM mengajar AS m INNER JOIN siswa AS s ON s.id = m.siswa_id WHERE s.kelas = ? AND m.mapel_id = ? LIMIT 1');
    mysqli_stmt_bind_param($check, 'si', $oldClass, $oldSubjectId);
    mysqli_stmt_execute($check);
    $editingAssignmentExists = mysqli_num_rows(mysqli_stmt_get_result($check)) > 0;
    mysqli_stmt_close($check);
    if (!$editingAssignmentExists) {
        $errorMessage = 'Data mengajar yang akan diedit tidak ditemukan.';
    }
}

include 'navbar.php';
?>
<main class="mx-auto max-w-3xl px-4 pt-32 pb-10 sm:px-6 lg:px-8">
  <form method="POST" class="space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 sm:p-8">
    <div>
      <h1 class="text-2xl font-bold text-gray-900"><?php echo $isEdit ? 'Edit Mengajar' : 'Tambah Mengajar'; ?></h1>
      <p class="mt-1 text-sm text-gray-500">Penugasan akan diterapkan kepada semua siswa di kelas yang dipilih.</p>
    </div>

    <?php if ($errorMessage !== ''): ?>
      <p role="alert" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <div>
      <label for="kelas" class="block text-sm font-medium text-gray-900">Pilih Kelas</label>
      <select id="kelas" name="kelas" required class="mt-2 block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600">
        <option value="">-- Pilih kelas --</option>
        <?php while ($class = mysqli_fetch_assoc($classResult)): ?>
          <option value="<?php echo htmlspecialchars($class['kelas'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedClass === $class['kelas'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['kelas'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label for="mapel_id" class="block text-sm font-medium text-gray-900">Pilih Guru dan Mata Pelajaran</label>
      <select id="mapel_id" name="mapel_id" required class="mt-2 block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600">
        <option value="">-- Pilih guru | mata pelajaran --</option>
        <?php while ($subject = mysqli_fetch_assoc($subjectResult)): ?>
          <option value="<?php echo (int) $subject['id']; ?>" <?php echo $selectedSubjectId === (int) $subject['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subject['nama_guru'] . ' | ' . $subject['nama_mapel'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endwhile; ?>
      </select>
      <p class="mt-1 text-xs text-gray-500">Pilihan berasal dari mata pelajaran yang sudah memiliki guru di data Mapel.</p>
    </div>

    <input type="hidden" name="is_edit" value="<?php echo $isEdit ? '1' : '0'; ?>">
    <?php if ($isEdit): ?>
      <input type="hidden" name="old_kelas" value="<?php echo htmlspecialchars($oldClass, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="old_mapel_id" value="<?php echo $oldSubjectId; ?>">
    <?php endif; ?>

    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
      <a href="daftar_mengajar.php" class="text-sm font-semibold text-gray-700">Batal</a>
      <button type="submit" <?php echo ($isEdit && !$editingAssignmentExists && $_SERVER['REQUEST_METHOD'] !== 'POST') ? 'disabled' : ''; ?> class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">Simpan</button>
    </div>
  </form>
</main>
</body>
</html>