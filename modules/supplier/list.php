<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';

checkLogin();

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY created_at DESC");
?>

<?php include __DIR__ . '../../../includes/head.php'; ?>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <?php include __DIR__ . '../../../includes/side.php'; ?>

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <?php include __DIR__ . '../../../includes/nav.php'; ?>

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">



                        <!-- Isi Utama -->

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>Daftar Supplier</h2>
                            <a href="add.php" class="btn btn-primary">Tambah Supplier</a>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success">Data berhasil disimpan!</div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Kontak</th>
                                            <th>Telepon</th>
                                            <th>Email</th>
                                            <th>Alamat</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1;
                                        while ($row = $suppliers->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                                <td><?= htmlspecialchars($row['contact_person']) ?></td>
                                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                                <td><?= htmlspecialchars($row['email']) ?></td>
                                                <td><?= htmlspecialchars($row['address']) ?></td>
                                                <td>
                                                    <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="edit.php?id=<?php echo $row['supplier_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                        <a href="delete.php?id=<?php echo $row['supplier_id']; ?>"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Yakin hapus?')">Hapus</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- / Isi Utama -->



                        <!-- / Content -->

                        <div class="content-backdrop fade"></div>
                    </div>
                    <!-- Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->


        <!-- Core JS -->
        <!-- build:js assets/vendor/js/core.js -->
        <?php include __DIR__ . '../../../includes/footer.php'; ?>

</body>

</html>