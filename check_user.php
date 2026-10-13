<?php
require_once __DIR__ . '/auth.php';
require_admin();
ob_start();
require_once __DIR__ . '/kon.php';
ob_end_clean();
header('Content-Type: application/json');

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';
$role = $_GET['role'] ?? '';

$response = ['match' => false];

if ($username !== '' && $password !== '' && ($role === 'siswa' || $role === 'guru')) {
    $username_esc = mysqli_real_escape_string($conn, $username);
    $role_esc = mysqli_real_escape_string($conn, $role);

    $sql_user = "SELECT id, password FROM users WHERE username = '$username_esc' AND role = '$role_esc' LIMIT 1";
    $result_user = mysqli_query($conn, $sql_user);
    $user = $result_user ? mysqli_fetch_assoc($result_user) : null;

    // support both password_hash()'ed accounts and legacy plain-text seed data
    if ($user && (password_verify($password, $user['password']) || hash_equals($user['password'], $password))) {
        $user_id = (int) $user['id'];

        if ($role === 'siswa') {
            $sql_profile = "SELECT id, nis AS kode, nama FROM siswa WHERE users_id = '$user_id' LIMIT 1";
        } else {
            $sql_profile = "SELECT id, nip AS kode, nama FROM guru WHERE user_id = '$user_id' LIMIT 1";
        }

        $result_profile = mysqli_query($conn, $sql_profile);
        $profile = $result_profile ? mysqli_fetch_assoc($result_profile) : null;

        if ($profile) {
            $response = [
                'match' => true,
                'id' => $profile['id'],
                'kode' => $profile['kode'],
                'nama' => $profile['nama'],
            ];
        }
    }
}

echo json_encode($response);
