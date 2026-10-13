<?php require_once __DIR__ . '/auth.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRdGVkppsfwNB0d2JxYA8hfHtTQQd_tcevK1KQzO6m7yQ&s" type="image/x-icon" href="/favicon.ico">
  <title>Website SIPN Kata Pa Gugun</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 antialiased">
  
  <?php include 'navbar.php'; ?>

    <!-- Membuat main sebagai 12 grid cols -->
  <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 grid grid-cols-12 gap-4">
    
    <form class="col-span-12 md:col-span-8 md:col-start-3 bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 rounded-xl" action="aksi_tambah_user.php" method="POST">
      <div class="space-y-12">

        <div class="border-b border-gray-900/10 pb-12">
          <h1 class="text-2xl font-extrabold text-gray-900">Form Tambah Guru</h1>
          <br>

          <div class="border-b border-gray-900/10 pb-12">
            <h2 class="text-lg font-semibold text-gray-900">Data Profil Guru</h2>

            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
              <div class="sm:col-span-4">
                <label for="nip" class="block text-sm/6 font-medium text-gray-900">Nomor Induk Pegawai (NIP) </label>
                <div class="mt-2">
                  <input id="nip" type="number" name="nip" autocomplete="off" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="Masukkan No Induk Pegawai | Contoh : 1234567890" required />
                </div>
              </div>

              <div class="sm:col-span-6">
                <label for="namalengkap" class="block text-sm/6 font-medium text-gray-900">Nama Lengkap</label>
                <div class="mt-2">
                  <input id="namalengkap" type="text" name="namalengkap" autocomplete="name" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" placeholder="Masukkan Nama Lengkap | Contoh : Asep jawa" required />
                </div>
              </div>

              <div class="sm:col-span-6">
                <label for="jeniskelamin" class="block text-sm/6 font-medium text-gray-900">Jenis Kelamin</label>

                <fieldset class="mt-2 flex items-center gap-x-6">
                  <div class="flex items-center gap-x-3">
                    <input id="laki-laki" type="radio" name="jeniskelamin" value="L" class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden" required />
                    <label for="laki-laki" class="block text-sm/6 font-medium text-gray-900">Laki Laki</label>
                  </div>
                  <div class="flex items-center gap-x-3">
                    <input id="perempuan" type="radio" name="jeniskelamin" value="P" class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white not-checked:before:hidden checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden" />
                    <label for="perempuan" class="block text-sm/6 font-medium text-gray-900">Perempuan</label>
                  </div>
                </fieldset>
              </div>
            </div>
          </div>

          <div class="sm:col-span-6">
            <h2 class="text-lg font-semibold text-gray-900">Data Kredensial Akun</h2>
            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
              <div class="sm:col-span-6">
                <label for="username" class="block text-sm/6 font-medium text-gray-900">Username</label>
                <div class="mt-2">
                  <input id="username" type="text" name="username" autocomplete="username" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
                </div>
              </div>

              <div class="sm:col-span-6">
                <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                <div class="mt-2">
                  <input id="password" type="password" name="password" autocomplete="new-password" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
                </div>
              </div>
            </div>

            <input type="hidden" name="role" value="guru">
            <input type="hidden" name="tipe_form" value="guru">
          </div>
        </div>
      </div>

      <div class="mt-6 flex items-center justify-end gap-x-6">
        <button type="button" class="text-sm/6 font-semibold text-gray-900">Cancel</button>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
      </div>
    </form>
  </main>
</body>
</html>


