<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get filter parameters from request
$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$location_id = $_GET['location_id'] ?? '';
$stock_condition = $_GET['stock_condition'] ?? '';

// Base SQL query
$sql = "SELECT p.*, c.category_name, u.unit_name, l.location_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN units u ON p.unit_id = u.unit_id
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

// Add stock condition filter
if (!empty($stock_condition)) {
    switch ($stock_condition) {
        case 'low':
            $sql .= " AND p.current_stock < p.safety_stock";
            break;
        case 'adequate':
            $sql .= " AND p.current_stock >= p.safety_stock";
            break;
        case 'critical':
            $sql .= " AND p.current_stock <= (p.safety_stock * 0.5)";
            break;
    }
}

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

// Get categories and locations for filter dropdowns
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1");
$locations = $conn->query("SELECT * FROM storage_locations WHERE is_active = 1");
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
                            <div>
                                <a href="add.php" class="btn btn-primary me-2">Tambah Produk</a>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                            </div>
                        </div>

                        <!-- Search Box -->
                        <div class="mb-3">
                            <form method="get" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                                    <?php if (!empty($search) || !empty($category_id) || !empty($location_id) || !empty($stock_condition)): ?>
                                        <a href="?" class="btn btn-outline-danger">Reset</a>
                                    <?php endif; ?>
                                </div>
                                <!-- Hidden fields to maintain other filters during search -->
                                <?php if (!empty($category_id)): ?>
                                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">
                                <?php endif; ?>
                                <?php if (!empty($location_id)): ?>
                                    <input type="hidden" name="location_id" value="<?php echo htmlspecialchars($location_id); ?>">
                                <?php endif; ?>
                                <?php if (!empty($stock_condition)): ?>
                                    <input type="hidden" name="stock_condition" value="<?php echo htmlspecialchars($stock_condition); ?>">
                                <?php endif; ?>
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
                            <?php if (!empty($category_id) || !empty($location_id) || !empty($stock_condition)): ?>
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
                                    if (!empty($stock_condition)) {
                                        $conditions = [
                                            'low' => 'Stok Rendah',
                                            'adequate' => 'Stok Cukup',
                                            'critical' => 'Stok Kritis'
                                        ];
                                        $active_filters[] = $conditions[$stock_condition];
                                    }
                                    echo implode(', ', $active_filters);
                                    ?>
                                    <a href="?" class="btn-close" aria-label="Close"></a>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No.</th>
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
                                        <?php
                                        $no = 1;
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()):
                                                // Highlight row if stock is low
                                                $row_class = '';
                                                if ($row['current_stock'] < $row['safety_stock']) {
                                                    $row_class = $row['current_stock'] <= ($row['safety_stock'] * 0.5)
                                                        ? 'table-danger'
                                                        : 'table-warning';
                                                }
                                        ?>
                                                <tr class="<?php echo $row_class; ?>">
                                                    <td class="text-center"><?php echo $no++; ?></td>
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
                                        <?php endwhile;
                                        } else {
                                            echo '<tr><td colspan="9" class="text-center">Tidak ada data ditemukan</td></tr>';
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
                        <h5 class="modal-title">Filter Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category_id">
                                <option value="">Semua Kategori</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($category_id == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

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
                            <label class="form-label">Kondisi Stok</label>
                            <select class="form-select" name="stock_condition">
                                <option value="">Semua Kondisi</option>
                                <option value="low" <?php echo ($stock_condition == 'low') ? 'selected' : ''; ?>>Stok Rendah (di bawah stok minimal)</option>
                                <option value="adequate" <?php echo ($stock_condition == 'adequate') ? 'selected' : ''; ?>>Stok Cukup</option>
                                <option value="critical" <?php echo ($stock_condition == 'critical') ? 'selected' : ''; ?>>Stok Kritis (≤50% stok minimal)</option>
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