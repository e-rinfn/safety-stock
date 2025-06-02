<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

$sql = "SELECT * FROM storage_locations WHERE is_active = 1 ORDER BY location_name";
$result = $conn->query($sql);
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
                            <h2>Daftar Lokasi</h2>
                            <a href="add.php" class="btn btn-primary">Tambah Lokasi</a>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success">Data berhasil disimpan!</div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Nama Lokasi</th>
                                            <th>Deskripsi</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="edit.php?id=<?php echo $row['location_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                        <a href="delete.php?id=<?php echo $row['location_id']; ?>"
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