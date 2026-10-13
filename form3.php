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
        
         <h1 class="text-2xl font-extrabold text-gray-900">Form Tambah User (ADMIN)</h1>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
         <h2 class="sm:col-span-6 text-lg font-semibold text-gray-900">Data Kredensial Akun </h2>

            <div class="sm:col-span-6">
              <label for="username" class="block text-sm/6 font-medium text-gray-900">Username</label>
              <div class="mt-2">
                <input id="username" type="text" name="username" autocomplete="username" oninput="cekUserOtomatis()" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
              <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
              <div class="mt-2">
                <input id="password" type="password" name="password" autocomplete="new-password" oninput="cekUserOtomatis()" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
                <label for="role" class="block text-sm/6 font-medium text-gray-900">Role</label>
                <div class="mt-2">
                  <select id="role" name="role" onchange="tampilform()" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required>
                      <option value="" selected disabled></option>
                      <option value="admin">Admin</option>
                      <option value="siswa">Siswa</option>
                      <option value="guru">Guru</option>
                  </select>
                </div>
              </div>

            <div id="area-form" class="contents"></div>

            <input type="hidden" id="tipe_form" name="tipe_form" value="user_saja">
        </div>

      <div class="mt-6 flex items-center justify-end gap-x-6">
        <button type="button" class="text-sm/6 font-semibold text-gray-900">Cancel</button>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
      </div>
    </form>
  </main>
</body>
<script>
function tampilform() {
    var nilai = document.getElementById("role").value;
    var container = document.getElementById("area-form");
    var tipeForm = document.getElementById("tipe_form");

    if (nilai === "siswa" || nilai === "guru") {
        tipeForm.value = nilai;
        var label = nilai === "siswa" ? "Siswa" : "Guru";
        var labelKode = nilai === "siswa" ? "NIS" : "NIP";
        var namaKodeField = nilai === "siswa" ? "nisn" : "nip";
        var idField = nilai === "siswa" ? "siswa_id" : "guru_id";

        container.innerHTML = `
            <h2 class="sm:col-span-6 text-lg font-semibold text-gray-900">Data Profil ${label}</h2>

            <div class="sm:col-span-6">
              <label for="kode_field" class="block text-sm/6 font-medium text-gray-900">${labelKode}</label>
              <div class="mt-2">
                <input id="kode_field" type="text" name="${namaKodeField}" placeholder="Otomatis terisi jika akun sudah terdaftar, atau isi manual jika data baru" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <div class="sm:col-span-6">
              <label for="namalengkap" class="block text-sm/6 font-medium text-gray-900">Nama</label>
              <div class="mt-2">
                <input id="namalengkap" type="text" name="namalengkap" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" required />
              </div>
            </div>

            <input type="hidden" id="${idField}" name="${idField}" value="">
        `;

        cekUserOtomatis();
    } else {
        // admin: tidak ada kolom tambahan
        tipeForm.value = "user_saja";
        container.innerHTML = "";
    }
}

function cekUserOtomatis() {
    var role = document.getElementById("role").value;
    var kodeInput = document.getElementById("kode_field");
    var namaInput = document.getElementById("namalengkap");

    if ((role !== "siswa" && role !== "guru") || !kodeInput || !namaInput) {
        return;
    }

    var username = document.getElementById("username").value.trim();
    var password = document.getElementById("password").value;
    var idInput = document.getElementById(role === "siswa" ? "siswa_id" : "guru_id");

    if (username === "" || password === "") {
        return;
    }

    // cocokkan username + password + role persis dengan akun yang sudah ada di tabel users
    fetch("check_user.php?username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password) + "&role=" + encodeURIComponent(role))
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.match) {
                kodeInput.value = data.kode;
                kodeInput.readOnly = true;
                namaInput.value = data.nama;
                namaInput.readOnly = true;
                idInput.value = data.id;
            } else if (kodeInput.readOnly) {
                // sebelumnya auto-terisi tapi sekarang tidak cocok lagi, kembalikan ke input manual
                kodeInput.value = "";
                kodeInput.readOnly = false;
                namaInput.value = "";
                namaInput.readOnly = false;
                idInput.value = "";
            }
        });
}

</script>
</html>


