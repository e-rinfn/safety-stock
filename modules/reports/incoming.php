<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get filter parameters from request
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$product_id = isset($_GET['product_id']) ? sanitizeInput($_GET['product_id']) : '';
$supplier_id = isset($_GET['supplier_id']) ? sanitizeInput($_GET['supplier_id']) : '';
$user_id = isset($_GET['user_id']) ? sanitizeInput($_GET['user_id']) : '';
$start_date = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : date('Y-m-d');

// Base SQL query
$sql = "SELECT pi.*, p.product_name, p.product_code, s.supplier_name, u.username 
        FROM product_incoming pi
        JOIN products p ON pi.product_id = p.product_id
        LEFT JOIN suppliers s ON pi.supplier_id = s.supplier_id
        JOIN users u ON pi.user_id = u.user_id
        WHERE DATE(pi.transaction_date) BETWEEN ? AND ?";

// Add search condition
if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND (p.product_code LIKE ? OR p.product_name LIKE ? OR pi.notes LIKE ? OR s.supplier_name LIKE ?)";
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

$sql .= " ORDER BY pi.transaction_date DESC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters
$param_types = 'ss';
$param_values = [$start_date, $end_date . ' 23:59:59']; // Include entire end day

if (!empty($search)) {
    $param_types .= 'ssss';
    array_push($param_values, $search_term, $search_term, $search_term, $search_term);
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

$stmt->bind_param($param_types, ...$param_values);
$stmt->execute();
$result = $stmt->get_result();

// Get total incoming
$total_sql = "SELECT SUM(quantity) as total FROM ($sql) as temp";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param($param_types, ...$param_values);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_incoming = $total_result->fetch_assoc()['total'] ?? 0;

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
                            <h2>Laporan Produk Masuk</h2>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="bx bx-filter-alt me-1"></i> Filter Lanjutan
                            </button>
                        </div>

                        <!-- Search and Date Filter -->
                        <form method="get" class="row g-3 align-items-end mb-4">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search"
                                        placeholder="Cari kode/nama barang, supplier, atau catatan..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <?php if (!empty($search) || !empty($product_id) || !empty($supplier_id) || !empty($user_id)): ?>
                                        <a href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"
                                            class="btn btn-outline-danger">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="<?php echo htmlspecialchars($start_date); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="<?php echo htmlspecialchars($end_date); ?>">
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                                <a href="incoming.php" class="btn btn-warning w-100">reset</a>
                            </div>
                        </form>

                        <!-- Summary Info -->
                        <div class="mb-3">
                            <h5>Periode: <span class="text-primary"><?php echo formatDate($start_date); ?> s/d <?php echo formatDate($end_date); ?></span></h5>
                            <h6>Total Barang Masuk: <span class="text-success"><?php echo number_format($total_incoming, 0); ?> item</span></h6>

                            <!-- Active Filters Summary -->
                            <?php if (!empty($product_id) || !empty($supplier_id) || !empty($user_id)): ?>
                                <div class="alert alert-info alert-dismissible fade show mt-2" role="alert">
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
                                    echo implode(', ', $active_filters);
                                    ?>
                                    <a href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>"
                                        class="btn-close" aria-label="Close"></a>
                                </div>
                            <?php endif; ?>
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
                                        <?php if ($result && $result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo formatDate($row['transaction_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                    <td><?php echo number_format($row['quantity'], 0); ?></td>
                                                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? '-'); ?></td>
                                                    <td><?php echo $row['purchase_price'] ? 'Rp ' . number_format($row['purchase_price'], 2) : '-'; ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['notes'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data ditemukan</td>
                                            </tr>
                                        <?php endif; ?>
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

        <!-- Advanced Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="get" action="">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Filter Lanjutan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Preserve existing search and date filters -->
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

                            <div class="mb-3">
                                <label class="form-label">Barang</label>
                                <select class="form-select" name="product_id">
                                    <option value="">Semua Barang</option>
                                    <?php while ($product = $products->fetch_assoc()): ?>
                                        <option value="<?php echo $product['product_id']; ?>"
                                            <?php echo ($product_id == $product['product_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['product_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Supplier</label>
                                <select class="form-select" name="supplier_id">
                                    <option value="">Semua Supplier</option>
                                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                                        <option value="<?php echo $supplier['supplier_id']; ?>"
                                            <?php echo ($supplier_id == $supplier['supplier_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pencatat</label>
                                <select class="form-select" name="user_id">
                                    <option value="">Semua Pencatat</option>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <option value="<?php echo $user['user_id']; ?>"
                                            <?php echo ($user_id == $user['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
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