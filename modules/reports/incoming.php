<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Filter by date
$start_date = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : date('Y-m-d');

$sql = "SELECT pi.*, p.product_name, p.product_code, s.supplier_name, u.username 
        FROM product_incoming pi
        JOIN products p ON pi.product_id = p.product_id
        LEFT JOIN suppliers s ON pi.supplier_id = s.supplier_id
        JOIN users u ON pi.user_id = u.user_id
        WHERE DATE(pi.transaction_date) BETWEEN '$start_date' AND '$end_date'
        ORDER BY pi.transaction_date DESC";

$result = $conn->query($sql);
$total_incoming = $conn->query("SELECT SUM(quantity) as total FROM ($sql) as temp")->fetch_assoc()['total'];
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
                            <h2>Laporan Produk Masuk</h2>
                        </div>
                        <form method="get" class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>

                        <div class="mb-3">
                            <h5>Periode: <span class="text-primary"><?php echo formatDate($start_date); ?> s/d <?php echo formatDate($end_date); ?></span></h5>
                            <h6>Total Barang Masuk: <span class="text-success"><?php echo $total_incoming; ?> item</span></h6>
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
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah</th>
                                            <th>Supplier</th>
                                            <th>Harga Beli</th>
                                            <th>Pencatat</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo formatDate($row['transaction_date']); ?></td>
                                                <td><?php echo $row['product_code']; ?></td>
                                                <td><?php echo $row['product_name']; ?></td>
                                                <td><?php echo $row['quantity']; ?></td>
                                                <td><?php echo $row['supplier_name'] ?? '-'; ?></td>
                                                <td><?php echo $row['purchase_price'] ? number_format($row['purchase_price'], 2) : '-'; ?></td>
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