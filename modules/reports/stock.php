<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Filter by category if provided
$category_id = isset($_GET['category_id']) ? sanitizeInput($_GET['category_id']) : null;

$sql = "SELECT p.*, c.category_name, u.unit_name, l.location_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        JOIN units u ON p.unit_id = u.unit_id
        LEFT JOIN storage_locations l ON p.location_id = l.location_id
        WHERE p.is_active = 1";

if ($category_id) {
    $sql .= " AND p.category_id = $category_id";
}

$sql .= " ORDER BY p.product_name";

$result = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1");
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
                        </div>
                        <form method="get" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="category_id" class="form-label">Filter Kategori:</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $category_id) ? 'selected' : ''; ?>>
                                            <?php echo $cat['category_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>

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
                                        <?php while ($row = $result->fetch_assoc()):
                                            $status = $row['current_stock'] < $row['safety_stock'] ? 'Kritis' : 'Aman';
                                            $status_class = $row['current_stock'] < $row['safety_stock'] ? 'text-danger fw-bold' : 'text-success fw-bold';
                                        ?>
                                            <tr>
                                                <td><?php echo $row['product_code']; ?></td>
                                                <td><?php echo $row['product_name']; ?></td>
                                                <td><?php echo $row['category_name'] ?? '-'; ?></td>
                                                <td><?php echo $row['current_stock']; ?></td>
                                                <td><?php echo $row['safety_stock']; ?></td>
                                                <td class="<?php echo $status_class; ?>"><?php echo $status; ?></td>
                                                <td><?php echo $row['unit_name']; ?></td>
                                                <td><?php echo $row['location_name'] ?? '-'; ?></td>
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