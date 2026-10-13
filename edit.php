<?php
require_once __DIR__ . '/auth.php';
require_admin();
include __DIR__ . '/kon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Updtae tabel users
    if ($password != '') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql_user = "UPDATE users SET username = '$username', password = '$hashed', role = '$role' WHERE id = '$id'";
    } else {
        $sql_user = "UPDATE users SET username = '$username', role = '$role' WHERE id = '$id'";
    }
    mysqli_query($conn, $sql_user);

    if ($role === 'siswa') {
        $siswa_id = $_POST['siswa_id'];
        $nis      = $_POST['nisn'];
        $nama     = $_POST['namalengkap'];
        $kelas    = $_POST['kelas'];
        $jk       = $_POST['jeniskelamin'];

        if ($siswa_id != '') {
            $sql_siswa = "UPDATE siswa SET nis = '$nis', nama = '$nama', kelas = '$kelas', jenis_kelamin = '$jk' WHERE id = '$siswa_id'";
        } else {
            $sql_siswa = "INSERT INTO siswa (id, nis, nama, kelas, jenis_kelamin, users_id) VALUES ('$id', '$nis', '$nama', '$kelas', '$jk', '$id')";
        }
        mysqli_query($conn, $sql_siswa);

    } elseif ($role === 'guru') {
        $guru_id = $_POST['guru_id'];
        $nip     = $_POST['nip'];
        $nama    = $_POST['namalengkap'];
        $jk      = $_POST['jeniskelamin'];

        if ($guru_id != '') {
            $sql_guru = "UPDATE guru SET nip = '$nip', nama = '$nama', jenis_kelamin = '$jk' WHERE id = '$guru_id'";
        } else {
            $sql_guru = "INSERT INTO guru (id, nip, nama, jenis_kelamin, user_id) VALUES ('$id', '$nip', '$nama', '$jk', '$id')";
        }
        mysqli_query($conn, $sql_guru);
    }

    $returnPage = $_POST['page'] ?? 'tabeluseraja.php';
    if (!in_array($returnPage, ['tabeluseraja.php', 'daftarsiswa.php', 'daftarguru.php'], true)) {
      $returnPage = 'tabeluseraja.php';
    }
    echo "<script>alert('Data berhasil diperbarui.'); window.location.href = " . json_encode($returnPage) . ";</script>";
    exit;
}

$id = $_GET['id'];
$returnPage = $_GET['page'] ?? 'tabeluseraja.php';
if (!in_array($returnPage, ['tabeluseraja.php', 'daftarsiswa.php', 'daftarguru.php'], true)) {
  $returnPage = 'tabeluseraja.php';
}

$sql_tampil = "SELECT u.*,
        s.id AS siswa_id, s.nis, s.kelas, s.jenis_kelamin AS siswa_jk, s.nama AS siswa_nama,
        g.id AS guru_id, g.nip, g.jenis_kelamin AS guru_jk, g.nama AS guru_nama
    FROM users u
    LEFT JOIN siswa s ON s.users_id = u.id
    LEFT JOIN guru g ON g.user_id = u.id
    WHERE u.id = '$id'";

$query = mysqli_query($conn, $sql_tampil);
$user = mysqli_fetch_assoc($query);

include __DIR__ . '/navbar.php';
?>


<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 grid grid-cols-12 gap-4">

    <form class="col-span-12 md:col-span-8 md:col-start-3 bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 rounded-xl" action="edit.php" method="POST">
      <div class="space-y-12">

        <h1 class="text-2xl font-extrabold text-gray-900">Form Edit User</h1>

        <h2 class="text-lg font-semibold text-gray-900">Data Kredensial Akun</h2>
        <div class="sm:col-span-6">
          <div class="sm:col-span-6">
            <label for="username" class="block text-sm/6 font-medium text-gray-900">Username</label>
            <div class="mt-2">
              <input id="username" type="text" name="username" value="<?php echo $user['username'] ?? ''; ?>" autocomplete="username" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
            </div>
          </div>

          <div class="sm:col-span-6">
            <label for="password" class="block text-sm/6 font-medium text-gray-900">Password Baru</label>
            <div class="mt-2">
              <input id="password" type="password" name="password" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengganti password" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
            </div>
          </div>

          <div class="sm:col-span-6">
            <label for="role" class="block text-sm/6 font-medium text-gray-900">Role</label>
            <div class="mt-2">
              <select id="role" disabled class="block w-full rounded-md bg-gray-100 px-3 py-1.5 text-base text-gray-500 outline-1 -outline-offset-1 outline-gray-300 sm:text-sm/6">
                <option value="admin" <?php echo (($user['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="guru" <?php echo (($user['role'] ?? '') === 'guru') ? 'selected' : ''; ?>>Guru</option>
                <option value="siswa" <?php echo (($user['role'] ?? '') === 'siswa') ? 'selected' : ''; ?>>Siswa</option>
              </select>
            </div>
          </div>

          <input type="hidden" name="role" value="<?php echo $user['role'] ?? ''; ?>">
          <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
          <input type="hidden" name="page" value="<?php echo htmlspecialchars($returnPage, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <?php if (($user['role'] ?? '') === 'siswa'): ?>
        <div class="border-t border-gray-900/10 pt-8">
          <h2 class="text-lg font-semibold text-gray-900">Data Profil Siswa</h2>
          <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-4">
              <label for="nisn" class="block text-sm/6 font-medium text-gray-900">Nomor Induk Siswa (NISN)</label>
              <div class="mt-2">
                <input id="nisn" type="text" name="nisn" value="<?php echo $user['nis'] ?? ''; ?>" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
              <label for="namalengkap" class="block text-sm/6 font-medium text-gray-900">Nama Lengkap</label>
              <div class="mt-2">
                <input id="namalengkap" type="text" name="namalengkap" value="<?php echo $user['siswa_nama'] ?? ''; ?>" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-4">
              <label for="kelas" class="block text-sm/6 font-medium text-gray-900">Kelas Siswa</label>
              <div class="mt-2">
                <input id="kelas" type="text" name="kelas" value="<?php echo $user['kelas'] ?? ''; ?>" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
              <label class="block text-sm/6 font-medium text-gray-900">Jenis Kelamin</label>
              <fieldset class="mt-2 flex items-center gap-x-6">
                <div class="flex items-center gap-x-3">
                  <input id="siswa-laki-laki" type="radio" name="jeniskelamin" value="L" <?php echo (($user['siswa_jk'] ?? '') === 'L') ? 'checked' : ''; ?> class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" required />
                  <label for="siswa-laki-laki" class="block text-sm/6 font-medium text-gray-900">Laki Laki</label>
                </div>
                <div class="flex items-center gap-x-3">
                  <input id="siswa-perempuan" type="radio" name="jeniskelamin" value="P" <?php echo (($user['siswa_jk'] ?? '') === 'P') ? 'checked' : ''; ?> class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" />
                  <label for="siswa-perempuan" class="block text-sm/6 font-medium text-gray-900">Perempuan</label>
                </div>
              </fieldset>
            </div>

            <input type="hidden" name="siswa_id" value="<?php echo $user['siswa_id'] ?? ''; ?>">
          </div>
        </div>

        
        <?php elseif (($user['role'] ?? '') === 'guru'): ?>
        <div class="border-t border-gray-900/10 pt-8">
          <h2 class="text-lg font-semibold text-gray-900">Data Profil Guru</h2>
          <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-4">
              <label for="nip" class="block text-sm/6 font-medium text-gray-900">Nomor Induk Pegawai (NIP)</label>
              <div class="mt-2">
                <input id="nip" type="text" name="nip" value="<?php echo $user['nip'] ?? ''; ?>" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
              <label for="namalengkap" class="block text-sm/6 font-medium text-gray-900">Nama Lengkap</label>
              <div class="mt-2">
                <input id="namalengkap" type="text" name="namalengkap" value="<?php echo $user['guru_nama'] ?? ''; ?>" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
              <label class="block text-sm/6 font-medium text-gray-900">Jenis Kelamin</label>
              <fieldset class="mt-2 flex items-center gap-x-6">
                <div class="flex items-center gap-x-3">
                  <input id="guru-laki-laki" type="radio" name="jeniskelamin" value="L" <?php echo (($user['guru_jk'] ?? '') === 'L') ? 'checked' : ''; ?> class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" required />
                  <label for="guru-laki-laki" class="block text-sm/6 font-medium text-gray-900">Laki Laki</label>
                </div>
                <div class="flex items-center gap-x-3">
                  <input id="guru-perempuan" type="radio" name="jeniskelamin" value="P" <?php echo (($user['guru_jk'] ?? '') === 'P') ? 'checked' : ''; ?> class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" />
                  <label for="guru-perempuan" class="block text-sm/6 font-medium text-gray-900">Perempuan</label>
                </div>
              </fieldset>
            </div>

            <input type="hidden" name="guru_id" value="<?php echo $user['guru_id'] ?? ''; ?>">
          </div>
        </div>
        <?php endif; ?>
      </div>


      <div class="mt-6 flex items-center justify-end gap-x-6">
        <a href="<?php echo htmlspecialchars($returnPage, ENT_QUOTES, 'UTF-8'); ?>" class="text-sm/6 font-semibold text-gray-900">Cancel</a>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
      </div>
    </form>
</main>