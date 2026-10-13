<!doctype html>
<html lang="en">
<?php include './koneksi.php'; ?>
<?php require_once __DIR__ . '/auth.php'; require_admin(); include './navbar.php'; ?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js"></script>
</head>

<body>


<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="mb-4 d-flex justify-content-center">Data Siswa Do While</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered border-secondary">
                    <thead class="table-dark">

                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Tanggal Lahir</th>
                            <th scope="col">Jenis Kelamin</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">Email</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query = "SELECT * FROM user ORDER BY id DESC";
                        $users = mysqli_query($conn, $query);

                        $no = 1;
                        $user = mysqli_fetch_assoc($users); 

                        if ($user) { 
                            do {
                        ?>
                                <tr>
                                    <th scope="no"><?php echo $no++; ?></th>
                                    <td><?php echo $user['nama']; ?></td>
                                    <td><?php echo $user['ttl']; ?></td>
                                    <td><?php echo $user['jenis_kelamin']; ?></td>
                                    <td><?php echo $user['alamat']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="edit.php?id=<?php echo $user['id']; ?>&page=siswadowhile.php" class="btn btn-sm btn-primary" title="Edit" aria-label="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $user['id']; ?>&page=siswadowhile.php" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                    
                                $user = mysqli_fetch_assoc($users);
                            } while ($user);
                        }
                        ?>
                    </tbody>
                </table>

    <!-- start content  -->




    <!-- end content -->


<?php include './footer.php'; ?>