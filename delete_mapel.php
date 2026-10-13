<?php
require_once __DIR__ . '/auth.php';
require_admin();
include_once __DIR__ . '/kon.php';

$role = $_GET['role'] ?? '';
$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>alert('ID tidak valid!'); window.location.href = 'daftar_mapel.php';</script>";
    exit;
} else {
    $querymapel = "DELETE FROM mapel WHERE id = $id";
}

if (mysqli_query($conn, $querymapel)) {
    echo "<script>alert('Data berhasil dihapus cuy!'); window.location.href = 'daftar_mapel.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data: " . addslashes(mysqli_error($conn)) . "'); window.location.href = 'daftar_mapel.php';</script>";
}
?>