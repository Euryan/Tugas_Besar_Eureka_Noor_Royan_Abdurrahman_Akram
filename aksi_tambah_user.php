<?php
require_once __DIR__ . '/auth.php';
require_admin();
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . '/kon.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
  $tipe_form = $_POST['tipe_form'];

    // Insert ke tabel users.
    $sql_users = "INSERT INTO users (username, password, role, created_at)
                  VALUES ('$username', '$password', '$role', NOW())";
    $query_users = mysqli_query($conn, $sql_users);

    if ($query_users) {
        $id_terakhir = mysqli_insert_id($conn);

      if ($tipe_form == 'user_saja') {
        echo "<script>
            alert('Data user berhasil disimpan.');
            window.location.href='tabeluseraja.php';
            </script>";
        exit;
      }

      if ($tipe_form == 'guru') {
        $guru_id = $_POST['guru_id'] ?? '';

        if ($guru_id !== '') {
            $guru_id = (int) $guru_id;
            $sql_guru = "UPDATE guru SET user_id = '$id_terakhir' WHERE id = '$guru_id'";
        } else {
            $nip = $_POST['nip'];
            $nama = $_POST['namalengkap'];
            $jenis_kelamin = $_POST['jeniskelamin'] ?? 'L';
            $sql_guru = "INSERT INTO guru (id, nip, nama, jenis_kelamin, user_id)
                         VALUES ('$id_terakhir', '$nip', '$nama', '$jenis_kelamin', '$id_terakhir')";
        }
        $query_guru = mysqli_query($conn, $sql_guru);

        if ($query_guru) {
            echo "<script>
                    alert('Data berhasil disimpan.');
                    window.location.href='daftarguru.php';
                  </script>";
            exit;
        }

        $error_guru = addslashes(mysqli_error($conn));
        echo "<script>
                alert('Gagal menyimpan data guru: $error_guru');
                window.location.href='form3.php';
              </script>";
        exit;
      }

      $siswa_id = $_POST['siswa_id'] ?? '';

      if ($siswa_id !== '') {
          $siswa_id = (int) $siswa_id;
          $sql_siswa = "UPDATE siswa SET users_id = '$id_terakhir' WHERE id = '$siswa_id'";
      } else {
          $nis = $_POST['nisn'];
          $nama = $_POST['namalengkap'];
          $kelas = $_POST['kelas'] ?? '-';
          $jenis_kelamin = $_POST['jeniskelamin'] ?? 'L';
          $sql_siswa = "INSERT INTO siswa (id, nis, nama, kelas, jenis_kelamin, users_id)
                        VALUES ('$id_terakhir', '$nis', '$nama', '$kelas', '$jenis_kelamin', '$id_terakhir')";
      }
        $query_siswa = mysqli_query($conn, $sql_siswa);

        if ($query_siswa) {
            echo "<script>
                    alert('Data berhasil disimpan.');
                    window.location.href='daftarsiswa.php';
                  </script>";
            exit;
        }

        $error_siswa = addslashes(mysqli_error($conn));
        echo "<script>
                alert('Gagal menyimpan data siswa: $error_siswa');
                window.location.href='formtambahsiswa.php';
              </script>";
        exit;
    }

    $error_users = addslashes(mysqli_error($conn));
    echo "<script>
            alert('Gagal menyimpan data ke tabel users: $error_users');
            window.location.href='formtambahsiswa.php';
          </script>";
    exit;
}
?>