<?php
require_once __DIR__ . '/auth.php';
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tailwind CDN</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>

</head>
<body>

<nav class="bg-white fixed w-full z-20 top-0 start-0 border-b border-default">
    <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
        <a href="table.php" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRdGVkppsfwNB0d2JxYA8hfHtTQQd_tcevK1KQzO6m7yQ&s" class="h-7" />
            <span class="self-center text-xl font-semibold whitespace-nowrap text-heading">SIPN</span>
        </a>
        <div class="flex items-center order-2 space-x-3 md:order-3 rtl:space-x-reverse">
            <button type="button" class="flex text-sm bg-neutral-primary rounded-full focus:ring-4 focus:ring-neutral-tertiary" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                <span class="sr-only">Open user menu</span>
                <!-- default guest avatar, dipakai selama user belum login -->
                <svg class="w-8 h-8 rounded-full text-gray-400 bg-gray-100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-7 9a7 7 0 1 1 14 0H3Z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44" id="user-dropdown">
                <?php if ($currentUser !== null): ?>
                <div class="px-4 py-3 text-sm border-b border-default">
                    <span class="block text-heading font-medium"><?php echo htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="block text-body truncate"><?php echo htmlspecialchars(ucfirst($currentUser['role']), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <ul class="p-2 text-sm text-body font-medium" aria-labelledby="user-menu-button">
                    <li><a href="profile.php" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Profil Saya</a></li>
                    <li><a href="logout.php" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Logout</a></li>
                </ul>
                <?php else: ?>
                <ul class="p-2 text-sm text-body font-medium" aria-labelledby="user-menu-button">
                    <li><a href="login.php" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Login</a></li>
                    <li><a href="register.php" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Register</a></li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
        <button data-collapse-toggle="navbar-menu" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-body rounded-lg md:hidden hover:bg-neutral-secondary-soft hover:text-heading focus:outline-none focus:ring-2 focus:ring-default" aria-controls="navbar-menu" aria-expanded="false">
            <span class="sr-only">Open main menu</span>
            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/></svg>
        </button>
        <div id="navbar-menu" class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1">
            <ul class="flex flex-col mt-4 font-medium md:flex-row md:mt-0 md:space-x-8 rtl:space-x-reverse">
                <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <li><a href="table.php" class="block py-2 px-3 text-heading hover:text-fg-brand border-b border-light hover:bg-neutral-secondary-soft md:hover:bg-transparent md:border-0 md:hover:text-fg-brand md:p-0" aria-current="page">Home</a></li>
                <?php endif; ?>
                <?php if ($currentUser && $currentUser['role'] === 'guru'): ?>
                <li><a href="daftarguru.php" class="block py-2 px-3 text-heading hover:text-fg-brand md:p-0">Daftar Guru</a></li>
                <li><a href="nilai.php" class="block py-2 px-3 text-heading hover:text-fg-brand md:p-0">Nilai Siswa</a></li>
                <?php elseif ($currentUser && $currentUser['role'] === 'siswa'): ?>
                <li><a href="daftarsiswa.php" class="block py-2 px-3 text-heading hover:text-fg-brand md:p-0">Daftar Siswa</a></li>
                <li><a href="nilai_siswa.php" class="block py-2 px-3 text-heading hover:text-fg-brand md:p-0">Nilai Saya</a></li>
                <?php endif; ?>
                <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <li>
                    <button id="dropdownUserButton" data-dropdown-toggle="dropdownUser" type="button" class="flex items-center justify-between w-full py-2 px-3 font-medium text-heading border-b border-light md:w-auto hover:bg-neutral-secondary-soft md:hover:bg-transparent md:border-0 md:hover:text-fg-brand md:p-0">
                        User
                        <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div id="dropdownUser" class="z-30 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-48 border border-gray-200">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownUserButton">
                            <li><a href="tabeluseraja.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Daftar User</a></li>
                            <li><a href="Tambahuser.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Tambah User</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <button id="dropdownSiswaButton" data-dropdown-toggle="dropdownSiswa" type="button" class="flex items-center justify-between w-full py-2 px-3 font-medium text-heading border-b border-light md:w-auto hover:bg-neutral-secondary-soft md:hover:bg-transparent md:border-0 md:hover:text-fg-brand md:p-0">
                        Siswa
                        <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div id="dropdownSiswa" class="z-30 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-48 border border-gray-200">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownSiswaButton">
                            <li><a href="daftarsiswa.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Daftar Siswa</a></li>
                            <li><a href="formtambahsiswa.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Tambah Siswa</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <button id="dropdownGuruButton" data-dropdown-toggle="dropdownGuru" type="button" class="flex items-center justify-between w-full py-2 px-3 font-medium text-heading border-b border-light md:w-auto hover:bg-neutral-secondary-soft md:hover:bg-transparent md:border-0 md:hover:text-fg-brand md:p-0">
                        Guru
                        <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div id="dropdownGuru" class="z-30 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-48 border border-gray-200">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownGuruButton">
                            <li><a href="daftarguru.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Daftar Guru</a></li>
                            <li><a href="formtambahguru.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Tambah Guru</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <button id="dropdownMapelButton" data-dropdown-toggle="dropdownMapel" type="button" class="flex items-center justify-between w-full py-2 px-3 font-medium text-heading border-b border-light md:w-auto hover:bg-neutral-secondary-soft md:hover:bg-transparent md:border-0 md:hover:text-fg-brand md:p-0">
                        Mapel
                        <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div id="dropdownMapel" class="z-30 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-48 border border-gray-200">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownMapelButton">
                            <li><a href="daftar_mapel.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Daftar Mapel</a></li>
                            <li><a href="tambah_mapel.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Tambah Mapel</a></li>
                        </ul>
                    </div>
                </li>
                <li>
                    <button id="dropdownMengajarButton" data-dropdown-toggle="dropdownMengajar" type="button" class="flex items-center justify-between w-full py-2 px-3 font-medium text-heading border-b border-light md:w-auto hover:bg-neutral-secondary-soft md:hover:bg-transparent md:border-0 md:hover:text-fg-brand md:p-0">
                        Mengajar
                        <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div id="dropdownMengajar" class="z-30 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-48 border border-gray-200">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownMengajarButton">
                            <li><a href="daftar_mengajar.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Daftar Mengajar</a></li>
                            <li><a href="tambah_mengajar.php" class="block px-4 py-2 hover:bg-neutral-secondary-medium">Tambah Mengajar</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

</body>
</html>

