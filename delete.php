<?php
require_once __DIR__ . '/auth.php';
require_admin();
include_once __DIR__ . '/kon.php';

$role = $_GET['role'] ?? '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$returnPage = $_GET['page'] ?? 'tabeluseraja.php';
if (!in_array($returnPage, ['tabeluseraja.php', 'daftarsiswa.php', 'daftarguru.php'], true)) {
    $returnPage = 'tabeluseraja.php';
}

function finishDelete(string $message, string $returnPage): void
{
    echo '<script>alert(' . json_encode($message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . '); window.location.href=' . json_encode($returnPage) . ';</script>';
    exit;
}

if ($id <= 0) {
    finishDelete('ID tidak valid.', $returnPage);
}

mysqli_begin_transaction($conn);

if ($role === 'siswa') {
    $stmt = mysqli_prepare($conn, 'DELETE FROM siswa WHERE users_id = ?');
} elseif ($role === 'guru') {
    $stmt = mysqli_prepare($conn, 'DELETE FROM guru WHERE user_id = ?');
} else {
    $stmt = null;
}

$success = $stmt === null || (mysqli_stmt_bind_param($stmt, 'i', $id) && mysqli_stmt_execute($stmt));
if ($stmt !== null) {
    mysqli_stmt_close($stmt);
}

if ($success) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
    $success = $stmt && mysqli_stmt_bind_param($stmt, 'i', $id) && mysqli_stmt_execute($stmt);
    if ($stmt) {
        mysqli_stmt_close($stmt);
    }
}

if ($success) {
    mysqli_commit($conn);
    finishDelete('Data berhasil dihapus.', $returnPage);
}

$error = mysqli_error($conn);
mysqli_rollback($conn);
finishDelete('Gagal menghapus data: ' . $error, $returnPage);
?>