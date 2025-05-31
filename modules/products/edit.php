<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$product_id = sanitizeInput($_GET['id']);

// Get product data
$product = $conn->query("SELECT * FROM products WHERE product_id = $product_id")->fetch_assoc();
if (!$product) {
    header("Location: list.php");
    exit();
}

// Get dropdown data
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1");
$units = $conn->query("SELECT * FROM units");
$locations = $conn->query("SELECT * FROM storage_locations WHERE is_active = 1");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = sanitizeInput($_POST['product_code']);
    $product_name = sanitizeInput($_POST['product_name']);
    $category_id = sanitizeInput($_POST['category_id']);
    $unit_id = sanitizeInput($_POST['unit_id']);
    $safety_stock = sanitizeInput($_POST['safety_stock']);
    $location_id = sanitizeInput($_POST['location_id']);
    $description = sanitizeInput($_POST['description']);

    $sql = "UPDATE products SET 
            product_code = '$product_code',
            product_name = '$product_name',
            category_id = $category_id,
            unit_id = $unit_id,
            safety_stock = $safety_stock,
            location_id = $location_id,
            description = '$description'
            WHERE product_id = $product_id";

    if ($conn->query($sql)) {
        header("Location: list.php?success=1");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
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
                            <h2>Edit Data Kategori</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="product_code" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_code" name="product_code"
                                            value="<?= htmlspecialchars($product['product_code']) ?>" required>
                                        <div class="invalid-feedback">Kode Barang wajib diisi.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="product_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_name" name="product_name"
                                            value="<?= htmlspecialchars($product['product_name']) ?>" required>
                                        <div class="invalid-feedback">Nama Barang wajib diisi.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Kategori</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Pilih Kategori</option>
                                            <?php while ($row = $categories->fetch_assoc()): ?>
                                                <option value="<?= $row['category_id'] ?>" <?= $row['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['category_name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="unit_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <select class="form-select" id="unit_id" name="unit_id" required>
                                            <option value="">Pilih Satuan</option>
                                            <?php while ($row = $units->fetch_assoc()): ?>
                                                <option value="<?= $row['unit_id'] ?>" <?= $row['unit_id'] == $product['unit_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['unit_name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="invalid-feedback">Satuan wajib dipilih.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="safety_stock" class="form-label">Safety Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="safety_stock" name="safety_stock"
                                            value="<?= htmlspecialchars($product['safety_stock']) ?>" min="0" step="0.01" required>
                                        <div class="invalid-feedback">Safety Stock wajib diisi dan tidak boleh kurang dari 0.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="location_id" class="form-label">Lokasi Penyimpanan</label>
                                        <select class="form-select" id="location_id" name="location_id">
                                            <option value="">Pilih Lokasi</option>
                                            <?php while ($row = $locations->fetch_assoc()): ?>
                                                <option value="<?= $row['location_id'] ?>" <?= $row['location_id'] == $product['location_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['location_name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="description" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
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