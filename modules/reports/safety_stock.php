<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

$sql = "SELECT p.product_code, p.product_name, p.current_stock, p.safety_stock, 
               u.unit_name, l.location_name
        FROM products p
        JOIN units u ON p.unit_id = u.unit_id
        LEFT JOIN storage_locations l ON p.location_id = l.location_id
        WHERE p.current_stock < p.safety_stock AND p.is_active = 1
        ORDER BY (p.current_stock/p.safety_stock) ASC";
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
                            <h2>Laporan Stok Kurang</h2>
                        </div>

                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Data berhasil disimpan!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Stok Saat Ini</th>
                                            <th>Safety Stock</th>
                                            <th>Defisit</th>
                                            <th>Satuan</th>
                                            <th>Lokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()):
                                            $deficit = $row['safety_stock'] - $row['current_stock'];
                                        ?>
                                            <tr class="<?php echo $deficit > 0 ? 'table-danger' : ''; ?>">
                                                <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                <td class="text-end"><?php echo $row['current_stock']; ?></td>
                                                <td class="text-end"><?php echo $row['safety_stock']; ?></td>
                                                <td class="text-end text-danger fw-bold"><?php echo $deficit; ?></td>
                                                <td><?php echo htmlspecialchars($row['unit_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['location_name'] ?? '-'); ?></td>
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