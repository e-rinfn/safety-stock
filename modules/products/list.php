<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

$sql = "SELECT p.*, c.category_name, u.unit_name, l.location_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN units u ON p.unit_id = u.unit_id
        LEFT JOIN storage_locations l ON p.location_id = l.location_id
        WHERE p.is_active = 1";
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
                            <h2>Daftar Produk</h2>
                            <a href="add.php" class="btn btn-primary">Tambah Produk</a>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Data berhasil disimpan!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Stok</th>
                                            <th>Stok Minimal</th>
                                            <th>Satuan</th>
                                            <th>Lokasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['category_name'] ?? '-'); ?></td>
                                                <td><?php echo number_format($row['current_stock'], 0); ?></td>
                                                <td><?php echo number_format($row['safety_stock'], 0); ?></td>
                                                <td><?php echo htmlspecialchars($row['unit_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['location_name'] ?? '-'); ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="edit.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                        <a href="delete.php?id=<?php echo $row['product_id']; ?>"
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