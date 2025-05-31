<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

$sql = "SELECT po.*, p.product_name, ot.type_name, u.username 
        FROM product_outgoing po
        JOIN products p ON po.product_id = p.product_id
        JOIN outgoing_types ot ON po.type_id = ot.type_id
        JOIN users u ON po.user_id = u.user_id
        ORDER BY po.transaction_date DESC";
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
                            <h2>Daftar Barang Keluar</h2>
                            <a href="add.php" class="btn btn-primary">Tambah Barang Keluar</a>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success">Data berhasil disimpan!</div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Barang</th>
                                            <th>Jumlah</th>
                                            <th>Tujuan</th>
                                            <th>Keterangan</th>
                                            <th>Pencatat</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo formatDate($row['transaction_date']); ?></td>
                                                <td><?php echo $row['product_name']; ?></td>
                                                <td><?php echo $row['quantity']; ?></td>
                                                <td><?php echo $row['type_name']; ?></td>
                                                <td><?php echo $row['destination'] ?? '-'; ?></td>
                                                <td><?php echo $row['username']; ?></td>
                                                <td><?php echo $row['notes'] ?? '-'; ?></td>
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