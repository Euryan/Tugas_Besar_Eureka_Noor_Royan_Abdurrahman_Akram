<?php
require_once __DIR__ . '/auth.php';

$currentUser = require_login();
require_once __DIR__ . '/kon.php';

function profile_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$profile = [
    'username' => $currentUser['username'],
    'role' => $currentUser['role'],
    'created_at' => '',
    'nama' => '',
    'kode' => '',
    'kelas' => '',
    'jenis_kelamin' => '',
];

$errorMessage = '';
$successMessage = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_success']);

$userStatement = mysqli_prepare(
    $conn,
    'SELECT username, role, created_at FROM users WHERE id = ? LIMIT 1'
);
mysqli_stmt_bind_param($userStatement, 'i', $currentUser['id']);
mysqli_stmt_execute($userStatement);
$userData = mysqli_fetch_assoc(mysqli_stmt_get_result($userStatement));
mysqli_stmt_close($userStatement);

if (!$userData) {
    http_response_code(404);
    exit('Akun tidak ditemukan.');
}

$profile = array_merge($profile, $userData);

if ($currentUser['role'] === 'guru') {
    $statement = mysqli_prepare(
        $conn,
        'SELECT nama, nip AS kode, jenis_kelamin FROM guru WHERE user_id = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($statement, 'i', $currentUser['id']);
    mysqli_stmt_execute($statement);
    $person = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
    mysqli_stmt_close($statement);

    if (!$person) {
        http_response_code(403);
        exit('Profil guru tidak ditemukan untuk akun ini.');
    }

    $profile = array_merge($profile, $person);
} elseif ($currentUser['role'] === 'siswa') {
    $statement = mysqli_prepare(
        $conn,
        'SELECT nama, nis AS kode, kelas, jenis_kelamin FROM siswa WHERE users_id = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($statement, 'i', $currentUser['id']);
    mysqli_stmt_execute($statement);
    $person = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
    mysqli_stmt_close($statement);

    if (!$person) {
        http_response_code(403);
        exit('Profil siswa tidak ditemukan untuk akun ini.');
    }

    $profile = array_merge($profile, $person);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token()) {
        $errorMessage = 'Sesi formulir tidak valid. Muat ulang halaman dan coba kembali.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['nama'] ?? '');
        $gender = $_POST['jenis_kelamin'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if ($username === '' || strlen($username) > 50) {
            $errorMessage = 'Username wajib diisi dan maksimal 50 karakter.';
        } elseif (
            $currentUser['role'] !== 'admin'
            && ($name === '' || strlen($name) > 100)
        ) {
            $errorMessage = 'Nama wajib diisi dan maksimal 100 karakter.';
        } elseif (
            $currentUser['role'] !== 'admin'
            && !in_array($gender, ['L', 'P'], true)
        ) {
            $errorMessage = 'Pilih jenis kelamin yang valid.';
        } elseif (
            $password !== ''
            && (strlen($password) < 8 || $password !== $passwordConfirmation)
        ) {
            $errorMessage = 'Password baru minimal 8 karakter dan harus sama dengan konfirmasi.';
        } else {
            $duplicateStatement = mysqli_prepare(
                $conn,
                'SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1'
            );
            mysqli_stmt_bind_param($duplicateStatement, 'si', $username, $currentUser['id']);
            mysqli_stmt_execute($duplicateStatement);
            $usernameTaken = mysqli_fetch_assoc(mysqli_stmt_get_result($duplicateStatement)) !== null;
            mysqli_stmt_close($duplicateStatement);

            if ($usernameTaken) {
                $errorMessage = 'Username tersebut sudah digunakan.';
            } else {
                mysqli_begin_transaction($conn);

                if ($password !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateUser = mysqli_prepare(
                        $conn,
                        'UPDATE users SET username = ?, password = ? WHERE id = ?'
                    );
                    mysqli_stmt_bind_param(
                        $updateUser,
                        'ssi',
                        $username,
                        $passwordHash,
                        $currentUser['id']
                    );
                } else {
                    $updateUser = mysqli_prepare(
                        $conn,
                        'UPDATE users SET username = ? WHERE id = ?'
                    );
                    mysqli_stmt_bind_param($updateUser, 'si', $username, $currentUser['id']);
                }

                $saved = $updateUser && mysqli_stmt_execute($updateUser);
                if ($updateUser) {
                    mysqli_stmt_close($updateUser);
                }

                if ($saved && $currentUser['role'] === 'guru') {
                    $updatePerson = mysqli_prepare(
                        $conn,
                        'UPDATE guru SET nama = ?, jenis_kelamin = ? WHERE user_id = ?'
                    );
                    $saved = $updatePerson
                        && mysqli_stmt_bind_param(
                            $updatePerson,
                            'ssi',
                            $name,
                            $gender,
                            $currentUser['id']
                        )
                        && mysqli_stmt_execute($updatePerson);

                    if ($updatePerson) {
                        mysqli_stmt_close($updatePerson);
                    }
                } elseif ($saved && $currentUser['role'] === 'siswa') {
                    $updatePerson = mysqli_prepare(
                        $conn,
                        'UPDATE siswa SET nama = ?, jenis_kelamin = ? WHERE users_id = ?'
                    );
                    $saved = $updatePerson
                        && mysqli_stmt_bind_param(
                            $updatePerson,
                            'ssi',
                            $name,
                            $gender,
                            $currentUser['id']
                        )
                        && mysqli_stmt_execute($updatePerson);

                    if ($updatePerson) {
                        mysqli_stmt_close($updatePerson);
                    }
                }

                if ($saved) {
                    mysqli_commit($conn);
                    $_SESSION['user']['username'] = $username;
                    $_SESSION['profile_success'] = 'Profil berhasil diperbarui.';
                    header('Location: profile.php');
                    exit;
                }

                mysqli_rollback($conn);
                $errorMessage = 'Gagal menyimpan perubahan profil.';
            }
        }

        if ($errorMessage !== '') {
            $profile['username'] = $username;
            if ($currentUser['role'] !== 'admin') {
                $profile['nama'] = $name;
                $profile['jenis_kelamin'] = $gender;
            }
        }
    }
}

$displayName = $profile['nama'] ?: $profile['username'];
$roleLabel = ucfirst($profile['role']);
$initial = strtoupper(substr((string) ($profile['nama'] ?: $profile['username']), 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | SIPN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="mx-auto max-w-6xl px-4 pb-12 pt-28 sm:px-6 lg:px-8">
        <!-- Page heading -->
        <header class="mb-8">
            <p class="text-sm font-semibold tracking-wide text-indigo-600">PENGATURAN AKUN</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Profil Saya</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base">
                Kelola informasi akun dan perbarui detail pribadi Anda di sini.
            </p>
        </header>

        <!-- Feedback messages -->
        <?php if ($errorMessage !== ''): ?>
            <div class="mb-6 flex gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm text-rose-800" role="alert">
                <span class="mt-0.5 font-bold" aria-hidden="true">!</span>
                <p><?php echo profile_h($errorMessage); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="mb-6 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-800" role="status">
                <span class="mt-0.5 font-bold" aria-hidden="true">✓</span>
                <p><?php echo profile_h($successMessage); ?></p>
            </div>
        <?php endif; ?>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(260px,0.8fr)_minmax(0,1.6fr)]">
            <!-- Profile summary -->
            <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="h-2 bg-gradient-to-r from-indigo-500 via-violet-500 to-sky-400"></div>
                <div class="p-6 sm:p-7">
                    <div class="flex items-center gap-4">
                        <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-2xl font-bold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                            <?php echo profile_h($initial); ?>
                        </div>
                        <div class="min-w-0">
                            <h2 class="break-words text-lg font-bold leading-snug text-slate-900">
                                <?php echo profile_h($displayName); ?>
                            </h2>
                            <p class="mt-1 break-all text-sm text-slate-500">
                                @<?php echo profile_h($profile['username']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                            <?php echo profile_h($roleLabel); ?>
                        </span>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Informasi akun</h3>
                        <dl class="mt-4 space-y-4">
                            <?php if ($profile['kode'] !== ''): ?>
                                <div>
                                    <dt class="text-sm text-slate-500">
                                        <?php echo $currentUser['role'] === 'guru' ? 'NIP' : 'NIS'; ?>
                                    </dt>
                                    <dd class="mt-1 break-words text-sm font-semibold text-slate-800">
                                        <?php echo profile_h($profile['kode']); ?>
                                    </dd>
                                </div>
                            <?php endif; ?>

                            <?php if ($profile['kelas'] !== ''): ?>
                                <div>
                                    <dt class="text-sm text-slate-500">Kelas</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-800">
                                        <?php echo profile_h($profile['kelas']); ?>
                                    </dd>
                                </div>
                            <?php endif; ?>

                            <div>
                                <dt class="text-sm text-slate-500">Bergabung sejak</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">
                                    <?php echo $profile['created_at'] !== '' ? profile_h(date('d M Y', strtotime($profile['created_at']))) : '—'; ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </aside>

            <!-- Profile form -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                    <h2 class="text-lg font-bold text-slate-900">Informasi Profil</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Perbarui detail akun yang dapat Anda ubah. NIS/NIP dan kelas dikelola oleh sistem sekolah.
                    </p>
                </div>

                <form method="post" class="p-6 sm:p-8">
                    <input type="hidden" name="csrf_token" value="<?php echo profile_h(csrf_token()); ?>">

                    <div class="space-y-5">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Username</span>
                            <input
                                name="username"
                                maxlength="50"
                                required
                                autocomplete="username"
                                value="<?php echo profile_h($profile['username']); ?>"
                                class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >
                            <span class="mt-1.5 block text-xs leading-5 text-slate-500">Username digunakan untuk masuk ke akun Anda.</span>
                        </label>

                        <?php if ($currentUser['role'] !== 'admin'): ?>
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Nama lengkap</span>
                                <input
                                    name="nama"
                                    maxlength="100"
                                    required
                                    autocomplete="name"
                                    value="<?php echo profile_h($profile['nama']); ?>"
                                    class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                >
                            </label>

                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Jenis kelamin</span>
                                <select
                                    name="jenis_kelamin"
                                    required
                                    class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                                >
                                    <option value="L" <?php echo $profile['jenis_kelamin'] === 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="P" <?php echo $profile['jenis_kelamin'] === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div class="my-7 border-t border-slate-100"></div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900">Keamanan akun</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Isi bagian ini hanya jika ingin mengganti password.
                        </p>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Password baru</span>
                            <input
                                name="password"
                                type="password"
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter"
                                class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-slate-700">Konfirmasi password</span>
                            <input
                                name="password_confirmation"
                                type="password"
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Ulangi password baru"
                                class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                            >
                        </label>
                    </div>

                    <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-slate-500">Pastikan informasi yang Anda masukkan sudah benar.</p>
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 active:bg-indigo-800 sm:w-auto"
                        >
                            Simpan perubahan
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
