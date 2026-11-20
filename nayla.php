<?php
require_once 'koneksi.php';

// Mengambil data siswa dari database
$sql = "SELECT
            users.username,
            users.role,
            nama AS nama,
            siswa.kelas
        FROM users
        INNER JOIN siswa ON users.id = siswa.id
        WHERE users.role = 'siswa'
        ORDER BY siswa.id ASC";

$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

$dataSiswa = [];

while ($row = mysqli_fetch_assoc($query)) {
    $dataSiswa[] = $row;
}

// Memanggil navbar milikmu
require_once 'navbar.php';
?>

<!-- CSS DATATABLES -->
<link rel="stylesheet"
href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css">

<style>
    body {
        background: #fff;
        font-family: Arial, sans-serif;
        color: #212529;
    }

    /* JUDUL */
    .judul {
        text-align: center;
        font-size: 38px;
        font-weight: 600;
        margin-top: 10px;
        margin-bottom: 25px;
    }

    /* UKURAN TABEL */
    .container-tabel {
        width: 92%;
        margin: 0 auto;
    }

    /* HEADER TABEL */
    #tabelSiswa thead th {
        background-color: #e9ecef;
        font-weight: bold;
        padding: 12px;
        border: 1px solid #dee2e6;
    }

    /* ISI TABEL */
    #tabelSiswa tbody td {
        padding: 12px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }

    /* WARNA BARIS GANJIL */
    #tabelSiswa tbody tr:nth-child(odd) {
        background-color: #d1e7e5;
    }

    /* WARNA BARIS GENAP */
    #tabelSiswa tbody tr:nth-child(even) {
        background-color: #fff;
    }

    /* NOMOR TEBAL */
    #tabelSiswa tbody td:first-child {
        font-weight: bold;
    }

    /* HOVER */
    #tabelSiswa tbody tr:hover {
        background-color: #c5e1df;
    }

    /* SEARCH DAN PILIHAN JUMLAH DATA */
    .dt-container .dt-search input,
    .dt-container .dt-length select {
        border: 1px solid #ced4da;
        border-radius: 5px;
        padding: 7px 10px;
        background: #fff;
    }

    /* PAGINATION */
    .dt-paging .dt-paging-button.current {
        background: #0d6efd !important;
        color: white !important;
        border: 1px solid #0d6efd !important;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .judul {
            font-size: 26px;
        }

        .container-tabel {
            width: 96%;
        }
    }
</style>

<!-- TABEL SISWA -->
<div class="container-tabel">

    <h1 class="judul">
        Daftar Siswa Kelas XI RPL 1
    </h1>

    <div class="table-responsive">

        <table id="tabelSiswa"
               class="table table-bordered table-hover">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $no = 1;

                foreach ($dataSiswa as $data) {
                ?>
                    <tr>
                        <td><?= $no++ ?></td>

                        <td>
                            <?= htmlspecialchars($data['username']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['role']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['nama'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($data['kelas'] ?? '') ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>

    </div>
</div>

<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelSiswa').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50],

        ordering: true,

        searching: true,

        autoWidth: false,

        language: {
            lengthMenu: "Tampilkan _MENU_ data per halaman",

            search: "Cari:",

            info: "Menampilkan halaman _PAGE_ dari _PAGES_",

            infoEmpty: "Tidak ada data",

            zeroRecords: "Data tidak ditemukan",

            emptyTable: "Belum ada data siswa",

            paginate: {
                previous: "Sebelumnya",
                next: "Selanjutnya"
            }
        }

    });

});
</script>