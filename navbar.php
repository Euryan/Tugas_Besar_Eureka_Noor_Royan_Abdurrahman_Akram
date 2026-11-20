<?php
require_once __DIR__ . '/auth.php';
$currentUser = current_user();
$navRole = $currentUser['role'] ?? '';
$navPage = basename($_SERVER['PHP_SELF']);

// Menu langsung (tanpa dropdown) per role
$navLinks = [];
if ($navRole === 'guru') {
    $navLinks = ['beranda.php' => 'Beranda', 'nilai.php' => 'Nilai Siswa', 'daftarguru.php' => 'Daftar Guru'];
} elseif ($navRole === 'siswa') {
    $navLinks = ['beranda.php' => 'Beranda', 'nilai_siswa.php' => 'Nilai Saya', 'daftarsiswa.php' => 'Teman Sekelas'];
} elseif ($navRole === 'admin') {
    $navLinks = ['home_admin.php' => 'Home'];
}

// Menu dropdown khusus admin
$navDropdowns = $navRole === 'admin' ? [
    'User' => ['table.php' => 'Semua Akun', 'tabeluseraja.php' => 'Daftar User', 'Tambahuser.php' => 'Tambah User'],
    'Siswa' => ['daftarsiswa.php' => 'Daftar Siswa', 'formtambahsiswa.php' => 'Tambah Siswa'],
    'Guru' => ['daftarguru.php' => 'Daftar Guru', 'formtambahguru.php' => 'Tambah Guru'],
    'Mapel' => ['daftar_mapel.php' => 'Daftar Mapel', 'tambah_mapel.php' => 'Tambah Mapel'],
    'Mengajar' => ['daftar_mengajar.php' => 'Daftar Mengajar', 'tambah_mengajar.php' => 'Tambah Mengajar'],
] : [];

$navHome = $currentUser ? role_home($navRole) : 'login.php';
$navInitial = $currentUser ? strtoupper(substr($currentUser['username'], 0, 1)) : '';

if (!function_exists('nav_link_class')) { function nav_link_class(bool $active): string
{
    return $active
        ? 'block rounded-full px-3 py-2 text-sm font-medium text-gray-900 bg-gray-100'
        : 'block rounded-full px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900';
} }
?>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>

<nav class="fixed inset-x-0 top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <a href="<?php echo htmlspecialchars($navHome, ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center gap-2.5">
            <img src="assets/images/logo.png" alt="Logo SIPN" class="h-9 w-auto object-contain">
            <span class="leading-tight">
                <span class="block text-base font-bold text-slate-900">SIPN</span>
                <span class="hidden text-[11px] font-medium text-slate-400 sm:block">Sistem Penilaian Siswa</span>
            </span>
        </a>

        <!-- Menu -->
        <div id="navbar-menu" class="absolute inset-x-0 top-16 hidden border-b border-slate-200 bg-white px-4 py-3 md:static md:block md:border-0 md:bg-transparent md:p-0">
            <ul class="flex flex-col gap-1 md:flex-row md:items-center md:gap-2">
                <?php foreach ($navLinks as $href => $label): ?>
                <li><a href="<?php echo $href; ?>" class="<?php echo nav_link_class($navPage === $href); ?>"><?php echo $label; ?></a></li>
                <?php endforeach; ?>

                <?php foreach ($navDropdowns as $label => $items): ?>
                <?php $dropId = 'drop' . $label; $isActive = array_key_exists($navPage, $items); ?>
                <li>
                    <button id="<?php echo $dropId; ?>Button" data-dropdown-toggle="<?php echo $dropId; ?>" type="button" class="flex w-full items-center justify-between gap-1 <?php echo nav_link_class($isActive); ?>">
                        <?php echo $label; ?>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div id="<?php echo $dropId; ?>" class="z-50 hidden w-48 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                        <ul class="text-sm text-slate-700" aria-labelledby="<?php echo $dropId; ?>Button">
                            <?php foreach ($items as $href => $text): ?>
                            <li><a href="<?php echo $href; ?>" class="block rounded-lg px-3 py-2 hover:bg-slate-50 <?php echo $navPage === $href ? 'font-semibold text-gray-900' : ''; ?>"><?php echo $text; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Akun -->
        <div class="flex items-center gap-2">
            <button type="button" id="user-menu-button" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom-end" aria-expanded="false" class="flex items-center gap-2 rounded-full border border-gray-200 p-0.5 pr-3 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <span class="sr-only">Buka menu pengguna</span>
                <?php if ($currentUser !== null): ?>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-black text-sm font-medium text-white"><?php echo htmlspecialchars($navInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="hidden text-sm font-medium text-slate-700 sm:block"><?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php else: ?>
                <svg class="h-8 w-8 rounded-full bg-slate-100 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-7 9a7 7 0 1 1 14 0H3Z" clip-rule="evenodd"/></svg>
                <?php endif; ?>
            </button>
            <div id="user-dropdown" class="z-50 hidden w-52 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                <?php if ($currentUser !== null): ?>
                <div class="border-b border-slate-100 px-3 py-2.5">
                    <p class="truncate text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs capitalize text-slate-500"><?php echo htmlspecialchars($navRole, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <ul class="pt-1 text-sm text-slate-700" aria-labelledby="user-menu-button">
                    <li><a href="profile.php" class="block rounded-lg px-3 py-2 hover:bg-slate-50">Profil Saya</a></li>
                    <li><a href="logout.php" class="block rounded-lg px-3 py-2 text-red-600 hover:bg-red-50">Logout</a></li>
                </ul>
                <?php else: ?>
                <ul class="text-sm text-slate-700" aria-labelledby="user-menu-button">
                    <li><a href="login.php" class="block rounded-lg px-3 py-2 hover:bg-slate-50">Login</a></li>
                    <li><a href="register.php" class="block rounded-lg px-3 py-2 hover:bg-slate-50">Register</a></li>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Tombol menu mobile -->
            <button data-collapse-toggle="navbar-menu" type="button" aria-controls="navbar-menu" aria-expanded="false" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 md:hidden">
                <span class="sr-only">Buka menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>
</nav>
