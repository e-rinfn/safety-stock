<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get filter parameters from request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$location_id = isset($_GET['location_id']) ? $_GET['location_id'] : '';
$severity = isset($_GET['severity']) ? $_GET['severity'] : '';

// Base SQL query
$sql = "SELECT p.product_code, p.product_name, p.current_stock, p.safety_stock, 
               u.unit_name, l.location_name,
               (p.safety_stock - p.current_stock) AS deficit,
               (p.current_stock/p.safety_stock) AS stock_ratio
        FROM products p
        JOIN units u ON p.unit_id = u.unit_id
        LEFT JOIN storage_locations l ON p.location_id = l.location_id
        WHERE p.current_stock < p.safety_stock AND p.is_active = 1";

// Add search condition
if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (p.product_code LIKE ? OR p.product_name LIKE ?)";
}

// Add location filter
if (!empty($location_id)) {
    $sql .= " AND p.location_id = ?";
}

// Add severity filter
if (!empty($severity)) {
    switch ($severity) {
        case 'critical':
            $sql .= " AND (p.current_stock/p.safety_stock) <= 0.3";
            break;
        case 'warning':
            $sql .= " AND (p.current_stock/p.safety_stock) BETWEEN 0.3 AND 0.7";
            break;
        case 'notice':
            $sql .= " AND (p.current_stock/p.safety_stock) > 0.7";
            break;
    }
}

$sql .= " ORDER BY stock_ratio ASC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if needed
if (!empty($search)) {
    if (!empty($location_id)) {
        $stmt->bind_param("ssi", $search_term, $search_term, $location_id);
    } else {
        $stmt->bind_param("ss", $search_term, $search_term);
    }
} elseif (!empty($location_id)) {
    $stmt->bind_param("i", $location_id);
}

// Execute the query
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fallback to simple query if prepare fails
    $result = $conn->query($sql);
}

// Get locations for filter dropdown
$locations = $conn->query("SELECT * FROM storage_locations WHERE is_active = 1 ORDER BY location_name");
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
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="bx bx-filter-alt me-1"></i> Filter
                            </button>
                        </div>

                        <!-- Search Box -->
                        <div class="mb-3">
                            <form method="get" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari kode/nama barang..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                                    <?php if (!empty($search) || !empty($location_id) || !empty($severity)): ?>
                                        <a href="?" class="btn btn-outline-danger">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Data berhasil disimpan!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Filter Summary -->
                            <?php if (!empty($location_id) || !empty($severity)): ?>
                                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                                    <strong>Filter Aktif:</strong>
                                    <?php
                                    $active_filters = [];
                                    if (!empty($location_id)) {
                                        $loc_name = $conn->query("SELECT location_name FROM storage_locations WHERE location_id = $location_id")->fetch_assoc()['location_name'];
                                        $active_filters[] = "Lokasi: $loc_name";
                                    }
                                    if (!empty($severity)) {
                                        $severity_names = [
                                            'critical' => 'Kritis (≤30%)',
                                            'warning' => 'Peringatan (30-70%)',
                                            'notice' => 'Perhatian (>70%)'
                                        ];
                                        $active_filters[] = "Tingkat: " . $severity_names[$severity];
                                    }
                                    echo implode(', ', $active_filters);
                                    ?>
                                    <a href="?" class="btn-close" aria-label="Close"></a>
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
                                            <th>% Stok</th>
                                            <th>Satuan</th>
                                            <th>Lokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()):
                                                $deficit = $row['deficit'];
                                                $percentage = round(($row['current_stock'] / $row['safety_stock']) * 100, 1);
                                                // Determine row class based on severity
                                                $row_class = '';
                                                if ($percentage <= 30) {
                                                    $row_class = 'table-danger';
                                                } elseif ($percentage <= 70) {
                                                    $row_class = 'table-warning';
                                                } else {
                                                    $row_class = 'table-info';
                                                }
                                        ?>
                                                <tr class="<?php echo $row_class; ?>">
                                                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                    <td class="text-end"><?php echo number_format($row['current_stock'], 0); ?></td>
                                                    <td class="text-end"><?php echo number_format($row['safety_stock'], 0); ?></td>
                                                    <td class="text-end fw-bold"><?php echo number_format($deficit, 0); ?></td>
                                                    <td class="text-end"><?php echo $percentage; ?>%</td>
                                                    <td><?php echo htmlspecialchars($row['unit_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['location_name'] ?? '-'); ?></td>
                                                </tr>
                                        <?php endwhile;
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">Tidak ada data stok kurang ditemukan</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- / Isi Utama -->
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

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="get" action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Laporan Stok Kurang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Penyimpanan</label>
                            <select class="form-select" name="location_id">
                                <option value="">Semua Lokasi</option>
                                <?php while ($location = $locations->fetch_assoc()): ?>
                                    <option value="<?php echo $location['location_id']; ?>" <?php echo ($location_id == $location['location_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location['location_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tingkat Keparahan</label>
                            <select class="form-select" name="severity">
                                <option value="">Semua Tingkat</option>
                                <option value="critical" <?php echo ($severity == 'critical') ? 'selected' : ''; ?>>Kritis (≤30% dari safety stock)</option>
                                <option value="warning" <?php echo ($severity == 'warning') ? 'selected' : ''; ?>>Peringatan (30-70% dari safety stock)</option>
                                <option value="notice" <?php echo ($severity == 'notice') ? 'selected' : ''; ?>>Perhatian (>70% dari safety stock)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <?php include __DIR__ . '../../../includes/footer.php'; ?>
</body>

</html>