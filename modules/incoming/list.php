<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get filter parameters from request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
$supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
$price_max = isset($_GET['price_max']) ? $_GET['price_max'] : '';

// Base SQL query
$sql = "SELECT pi.*, p.product_name, s.supplier_name, u.username 
        FROM product_incoming pi
        JOIN products p ON pi.product_id = p.product_id
        LEFT JOIN suppliers s ON pi.supplier_id = s.supplier_id
        JOIN users u ON pi.user_id = u.user_id
        WHERE 1=1";

// Add search condition
if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (p.product_name LIKE ? OR pi.notes LIKE ? OR s.supplier_name LIKE ?)";
}

// Add product filter
if (!empty($product_id)) {
    $sql .= " AND pi.product_id = ?";
}

// Add supplier filter
if (!empty($supplier_id)) {
    $sql .= " AND pi.supplier_id = ?";
}

// Add user filter
if (!empty($user_id)) {
    $sql .= " AND pi.user_id = ?";
}

// Add date range filter
if (!empty($date_from)) {
    $sql .= " AND pi.transaction_date >= ?";
}
if (!empty($date_to)) {
    $sql .= " AND pi.transaction_date <= ?";
}

// Add price range filter
if (!empty($price_min)) {
    $sql .= " AND pi.purchase_price >= ?";
}
if (!empty($price_max)) {
    $sql .= " AND pi.purchase_price <= ?";
}

$sql .= " ORDER BY pi.transaction_date DESC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if needed
$param_types = '';
$param_values = [];

if (!empty($search)) {
    $param_types .= 'sss';
    array_push($param_values, $search_term, $search_term, $search_term);
}

if (!empty($product_id)) {
    $param_types .= 'i';
    array_push($param_values, $product_id);
}

if (!empty($supplier_id)) {
    $param_types .= 'i';
    array_push($param_values, $supplier_id);
}

if (!empty($user_id)) {
    $param_types .= 'i';
    array_push($param_values, $user_id);
}

if (!empty($date_from)) {
    $param_types .= 's';
    array_push($param_values, $date_from);
}

if (!empty($date_to)) {
    $param_types .= 's';
    array_push($param_values, $date_to . ' 23:59:59'); // Include entire end day
}

if (!empty($price_min)) {
    $param_types .= 'd';
    array_push($param_values, $price_min);
}

if (!empty($price_max)) {
    $param_types .= 'd';
    array_push($param_values, $price_max);
}

if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$param_values);
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
$products = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY product_name");
$suppliers = $conn->query("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY supplier_name");
$users = $conn->query("SELECT * FROM users WHERE is_active = 1 ORDER BY username");
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
                            <h2>Daftar Barang Masuk</h2>
                            <div>
                                <a href="add.php" class="btn btn-primary me-2">Tambah Barang Masuk</a>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                            </div>
                        </div>

                        <!-- Search Box -->
                        <div class="mb-3">
                            <form method="get" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari barang/supplier/keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                                    <?php if (!empty($search) || !empty($product_id) || !empty($supplier_id) || !empty($user_id) || !empty($date_from) || !empty($date_to) || !empty($price_min) || !empty($price_max)): ?>
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
                            <?php if (!empty($product_id) || !empty($supplier_id) || !empty($user_id) || !empty($date_from) || !empty($date_to) || !empty($price_min) || !empty($price_max)): ?>
                                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                                    <strong>Filter Aktif:</strong>
                                    <?php
                                    $active_filters = [];
                                    if (!empty($product_id)) {
                                        $product_name = $conn->query("SELECT product_name FROM products WHERE product_id = $product_id")->fetch_assoc()['product_name'];
                                        $active_filters[] = "Barang: $product_name";
                                    }
                                    if (!empty($supplier_id)) {
                                        $supplier_name = $conn->query("SELECT supplier_name FROM suppliers WHERE supplier_id = $supplier_id")->fetch_assoc()['supplier_name'];
                                        $active_filters[] = "Supplier: $supplier_name";
                                    }
                                    if (!empty($user_id)) {
                                        $username = $conn->query("SELECT username FROM users WHERE user_id = $user_id")->fetch_assoc()['username'];
                                        $active_filters[] = "Pencatat: $username";
                                    }
                                    if (!empty($date_from)) {
                                        $active_filters[] = "Dari: " . formatDate($date_from, 'd/m/Y');
                                    }
                                    if (!empty($date_to)) {
                                        $active_filters[] = "Sampai: " . formatDate($date_to, 'd/m/Y');
                                    }
                                    if (!empty($price_min)) {
                                        $active_filters[] = "Harga Min: " . number_format($price_min, 2);
                                    }
                                    if (!empty($price_max)) {
                                        $active_filters[] = "Harga Max: " . number_format($price_max, 2);
                                    }
                                    echo implode(', ', $active_filters);
                                    ?>
                                    <a href="?" class="btn-close" aria-label="Close"></a>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Barang</th>
                                            <th>Jumlah</th>
                                            <th>Supplier</th>
                                            <th>Harga Beli</th>
                                            <th>Pencatat</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><?php echo formatDate($row['transaction_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                    <td class="text-center"><?php echo number_format($row['quantity'], 0); ?></td>
                                                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? '-'); ?></td>
                                                    <td><?php echo $row['purchase_price'] ? 'Rp ' . number_format($row['purchase_price'], 2) : '-'; ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['notes'] ?? '-'); ?></td>
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
        <div class="modal-dialog modal-lg">
            <form method="get" action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Barang Masuk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barang</label>
                                <select class="form-select" name="product_id">
                                    <option value="">Semua Barang</option>
                                    <?php while ($product = $products->fetch_assoc()): ?>
                                        <option value="<?php echo $product['product_id']; ?>" <?php echo ($product_id == $product['product_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['product_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Supplier</label>
                                <select class="form-select" name="supplier_id">
                                    <option value="">Semua Supplier</option>
                                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                                        <option value="<?php echo $supplier['supplier_id']; ?>" <?php echo ($supplier_id == $supplier['supplier_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pencatat</label>
                                <select class="form-select" name="user_id">
                                    <option value="">Semua Pencatat</option>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <option value="<?php echo $user['user_id']; ?>" <?php echo ($user_id == $user['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga Minimum</label>
                                <input type="number" class="form-control" name="price_min" placeholder="0.00" step="0.01" value="<?php echo htmlspecialchars($price_min); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga Maksimum</label>
                                <input type="number" class="form-control" name="price_max" placeholder="0.00" step="0.01" value="<?php echo htmlspecialchars($price_max); ?>">
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