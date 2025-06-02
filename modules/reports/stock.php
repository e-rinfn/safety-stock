<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get filter parameters from request
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? sanitizeInput($_GET['category_id']) : '';
$location_id = isset($_GET['location_id']) ? sanitizeInput($_GET['location_id']) : '';
$stock_status = isset($_GET['stock_status']) ? sanitizeInput($_GET['stock_status']) : '';

// Base SQL query with JOINs
$sql = "SELECT p.*, c.category_name, u.unit_name, l.location_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        JOIN units u ON p.unit_id = u.unit_id
        LEFT JOIN storage_locations l ON p.location_id = l.location_id
        WHERE p.is_active = 1";

// Add search condition
if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (p.product_code LIKE ? OR p.product_name LIKE ?)";
}

// Add category filter
if (!empty($category_id)) {
    $sql .= " AND p.category_id = ?";
}

// Add location filter
if (!empty($location_id)) {
    $sql .= " AND p.location_id = ?";
}

// Add stock status filter
if (!empty($stock_status)) {
    switch ($stock_status) {
        case 'critical':
            $sql .= " AND p.current_stock < p.safety_stock";
            break;
        case 'adequate':
            $sql .= " AND p.current_stock >= p.safety_stock";
            break;
        case 'very_low':
            $sql .= " AND p.current_stock <= (p.safety_stock * 0.3)";
            break;
    }
}

$sql .= " ORDER BY p.product_name";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if needed
if (!empty($search)) {
    if (!empty($category_id) && !empty($location_id)) {
        $stmt->bind_param("ssii", $search_term, $search_term, $category_id, $location_id);
    } elseif (!empty($category_id)) {
        $stmt->bind_param("ssi", $search_term, $search_term, $category_id);
    } elseif (!empty($location_id)) {
        $stmt->bind_param("ssi", $search_term, $search_term, $location_id);
    } else {
        $stmt->bind_param("ss", $search_term, $search_term);
    }
} elseif (!empty($category_id) && !empty($location_id)) {
    $stmt->bind_param("ii", $category_id, $location_id);
} elseif (!empty($category_id)) {
    $stmt->bind_param("i", $category_id);
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

// Get filter options for dropdowns
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY category_name");
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
                            <h2>Laporan Stok Produk</h2>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="bx bx-filter-alt me-1"></i> Filter Lanjutan
                            </button>
                        </div>

                        <!-- Search Box -->
                        <div class="mb-4">
                            <form method="get" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari kode atau nama produk..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                                    <?php if (!empty($search) || !empty($category_id) || !empty($location_id) || !empty($stock_status)): ?>
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
                            <?php if (!empty($category_id) || !empty($location_id) || !empty($stock_status)): ?>
                                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                                    <strong>Filter Aktif:</strong>
                                    <?php
                                    $active_filters = [];
                                    if (!empty($category_id)) {
                                        $cat_name = $conn->query("SELECT category_name FROM categories WHERE category_id = $category_id")->fetch_assoc()['category_name'];
                                        $active_filters[] = "Kategori: $cat_name";
                                    }
                                    if (!empty($location_id)) {
                                        $loc_name = $conn->query("SELECT location_name FROM storage_locations WHERE location_id = $location_id")->fetch_assoc()['location_name'];
                                        $active_filters[] = "Lokasi: $loc_name";
                                    }
                                    if (!empty($stock_status)) {
                                        $statuses = [
                                            'critical' => 'Stok Kritis',
                                            'adequate' => 'Stok Cukup',
                                            'very_low' => 'Stok Sangat Rendah'
                                        ];
                                        $active_filters[] = $statuses[$stock_status];
                                    }
                                    echo implode(', ', $active_filters);
                                    ?>
                                    <a href="?" class="btn-close" aria-label="Close"></a>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Stok</th>
                                            <th>Stok Minimum</th>
                                            <th>Status</th>
                                            <th>Satuan</th>
                                            <th>Lokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()):
                                                // Determine stock status
                                                if ($row['current_stock'] <= ($row['safety_stock'] * 0.3)) {
                                                    $status = 'Sangat Rendah';
                                                    $status_class = 'text-danger fw-bold';
                                                } elseif ($row['current_stock'] < $row['safety_stock']) {
                                                    $status = 'Kritis';
                                                    $status_class = 'text-warning fw-bold';
                                                } else {
                                                    $status = 'Aman';
                                                    $status_class = 'text-success fw-bold';
                                                }
                                        ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['category_name'] ?? '-'); ?></td>
                                                    <td><?php echo number_format($row['current_stock'], 0); ?></td>
                                                    <td><?php echo number_format($row['safety_stock'], 0); ?></td>
                                                    <td class="<?php echo $status_class; ?>"><?php echo $status; ?></td>
                                                    <td><?php echo htmlspecialchars($row['unit_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['location_name'] ?? '-'); ?></td>
                                                </tr>
                                        <?php endwhile;
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">Tidak ada data ditemukan</td></tr>';
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
                        <h5 class="modal-title">Filter Laporan Stok</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="category_id">
                                    <option value="">Semua Kategori</option>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category_id == $cat['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lokasi Penyimpanan</label>
                                <select class="form-select" name="location_id">
                                    <option value="">Semua Lokasi</option>
                                    <?php while ($loc = $locations->fetch_assoc()): ?>
                                        <option value="<?php echo $loc['location_id']; ?>" <?php echo ($location_id == $loc['location_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($loc['location_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status Stok</label>
                                <select class="form-select" name="stock_status">
                                    <option value="">Semua Status</option>
                                    <option value="critical" <?php echo ($stock_status == 'critical') ? 'selected' : ''; ?>>Stok Kritis (di bawah minimum)</option>
                                    <option value="very_low" <?php echo ($stock_status == 'very_low') ? 'selected' : ''; ?>>Stok Sangat Rendah (≤30% minimum)</option>
                                    <option value="adequate" <?php echo ($stock_status == 'adequate') ? 'selected' : ''; ?>>Stok Cukup</option>
                                </select>
                            </div>
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