<?php
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'functions/helpers.php';

checkLogin();
?>



<?php include __DIR__ . '/includes/head.php'; ?>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <?php include __DIR__ . '/includes/side.php'; ?>

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <?php include __DIR__ . '/includes/nav.php'; ?>

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row mb-4">
                            <!-- Total Products -->
                            <div class="col-md-3">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Total Produk</h5>
                                        <?php
                                        $sql = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
                                        $result = $conn->query($sql);
                                        $total_products = $result->fetch_assoc()['total'];
                                        ?>
                                        <h2 class="card-text"><?php echo $total_products; ?></h2>
                                        <a href="modules/products/list.php" class="text-white">Lihat detail</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Critical Stock -->
                            <div class="col-md-3">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Stok Kritis</h5>
                                        <?php
                                        $sql = "SELECT COUNT(*) as critical FROM products 
                            WHERE current_stock < safety_stock AND is_active = 1";
                                        $result = $conn->query($sql);
                                        $critical_stock = $result->fetch_assoc()['critical'];
                                        ?>
                                        <h2 class="card-text"><?php echo $critical_stock; ?></h2>
                                        <a href="modules/stock/list.php" class="text-white">Lihat detail</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Incoming -->
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Barang Masuk (7 hari)</h5>
                                        <?php
                                        $sql = "SELECT COUNT(*) as incoming FROM product_incoming 
                            WHERE transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                        $result = $conn->query($sql);
                                        $recent_incoming = $result->fetch_assoc()['incoming'];
                                        ?>
                                        <h2 class="card-text"><?php echo $recent_incoming; ?></h2>
                                        <a href="modules/incoming/list.php" class="text-white">Lihat detail</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Outgoing -->
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h5 class="card-title text-white">Barang Keluar (7 hari)</h5>
                                        <?php
                                        $sql = "SELECT COUNT(*) as outgoing FROM product_outgoing 
                            WHERE transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                                        $result = $conn->query($sql);
                                        $recent_outgoing = $result->fetch_assoc()['outgoing'];
                                        ?>
                                        <h2 class="card-text"><?php echo $recent_outgoing; ?></h2>
                                        <a href="modules/outgoing/list.php" class="text-white">Lihat detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Critical Stock List -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="text-white mb-0">Stok Kritis</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        $sql = "SELECT p.product_id, p.product_code, p.product_name, p.current_stock, p.safety_stock, 
                                                    u.unit_name, l.location_name
                                                FROM products p
                                                JOIN units u ON p.unit_id = u.unit_id
                                                LEFT JOIN storage_locations l ON p.location_id = l.location_id
                                                WHERE p.current_stock < p.safety_stock AND p.is_active = 1
                                                ORDER BY (p.current_stock/p.safety_stock) ASC
                                                LIMIT 10";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0): ?>
                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Kode</th>
                                                            <th>Produk</th>
                                                            <th>Stok</th>
                                                            <th>Safety</th>
                                                            <th>Lokasi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($row = $result->fetch_assoc()):
                                                            $deficit = $row['safety_stock'] - $row['current_stock'];
                                                            $percentage = ($row['current_stock'] / $row['safety_stock']) * 100;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $row['product_code']; ?></td>
                                                                <td>
                                                                    <a href="modules/products/edit.php?id=<?php echo $row['product_id']; ?>">
                                                                        <?php echo $row['product_name']; ?>
                                                                    </a>
                                                                </td>
                                                                <td>
                                                                    <div class="progress" style="height: 20px;">
                                                                        <div class="progress-bar bg-danger"
                                                                            role="progressbar"
                                                                            style="width: <?php echo $percentage; ?>%"
                                                                            aria-valuenow="<?php echo $percentage; ?>"
                                                                            aria-valuemin="0"
                                                                            aria-valuemax="100">
                                                                            <?php echo (int)$row['current_stock']; ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td><?php echo (int)$row['safety_stock']; ?></td>
                                                                <td><?php echo $row['location_name'] ?? '-'; ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <a href="modules/reports/safety_stock.php" class="btn btn-sm btn-warning mt-3">Lihat Semua Stok Kritis</a>
                                        <?php else: ?>
                                            <p class="text-success mb-0 mt-3">Tidak ada stok kritis saat ini.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activities -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="text-white mb-0">Aktivitas Terakhir</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            // Get recent incoming
                                            $sql_incoming = "SELECT pi.transaction_date, p.product_name, pi.quantity, u.username
                                        FROM product_incoming pi
                                        JOIN products p ON pi.product_id = p.product_id
                                        JOIN users u ON pi.user_id = u.user_id
                                        ORDER BY pi.transaction_date DESC
                                        LIMIT 3";
                                            $result_incoming = $conn->query($sql_incoming);

                                            while ($row = $result_incoming->fetch_assoc()): ?>
                                                <li class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>Barang Masuk</strong>: <?php echo $row['product_name']; ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo formatDate($row['transaction_date']); ?></small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-success">+<?php echo (int)$row['quantity']; ?></span>
                                                            <br>
                                                            <small>Oleh: <?php echo $row['username']; ?></small>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endwhile; ?>

                                            <?php
                                            // Get recent outgoing
                                            $sql_outgoing = "SELECT po.transaction_date, p.product_name, po.quantity, u.username, ot.type_name
                                        FROM product_outgoing po
                                        JOIN products p ON po.product_id = p.product_id
                                        JOIN users u ON po.user_id = u.user_id
                                        JOIN outgoing_types ot ON po.type_id = ot.type_id
                                        ORDER BY po.transaction_date DESC
                                        LIMIT 3";
                                            $result_outgoing = $conn->query($sql_outgoing);

                                            while ($row = $result_outgoing->fetch_assoc()): ?>
                                                <li class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>Barang Keluar</strong>: <?php echo $row['product_name']; ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo formatDate($row['transaction_date']); ?></small>
                                                            <br>
                                                            <small>Tujuan: <?php echo $row['type_name']; ?></small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-danger">-<?php echo (int)$row['quantity']; ?></span>
                                                            <br>
                                                            <small>Oleh: <?php echo $row['username']; ?></small>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                        <div class="mt-3">
                                            <a href="modules/incoming/list.php" class="btn btn-sm btn-primary me-2">Lihat Barang Masuk</a>
                                            <a href="modules/outgoing/list.php" class="btn btn-sm btn-secondary">Lihat Barang Keluar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        <script src="/safety-stock/assets/vendor/libs/jquery/jquery.js"></script>
        <script src="/safety-stock/assets/vendor/libs/popper/popper.js"></script>
        <script src="/safety-stock/assets/vendor/js/bootstrap.js"></script>
        <script src="/safety-stock/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

        <script src="/safety-stock/assets/vendor/js/menu.js"></script>
        <!-- endbuild -->

        <!-- Vendors JS -->
        <script src="/safety-stock/assets/vendor/libs/apex-charts/apexcharts.js"></script>

        <!-- Main JS -->
        <script src="/safety-stock/assets/js/main.js"></script>

        <!-- Page JS -->
        <script src="/safety-stock/assets/js/dashboards-analytics.js"></script>

        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>