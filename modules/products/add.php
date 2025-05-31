<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../functions/helpers.php';

checkLogin();

// Cek apakah kolom is_active ada di tabel categories dan storage_locations
function hasIsActiveColumn($conn, $table)
{
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'is_active'");
    return $result && $result->num_rows > 0;
}

$categoryQuery = hasIsActiveColumn($conn, 'categories') ?
    "SELECT * FROM categories WHERE is_active = 1" :
    "SELECT * FROM categories";

$locationQuery = hasIsActiveColumn($conn, 'storage_locations') ?
    "SELECT * FROM storage_locations WHERE is_active = 1" :
    "SELECT * FROM storage_locations";

$categories = $conn->query($categoryQuery);
$units = $conn->query("SELECT * FROM units");
$locations = $conn->query($locationQuery);

// Inisialisasi error
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = sanitizeInput($_POST['product_code']);
    $product_name = sanitizeInput($_POST['product_name']);
    $category_id = (int) $_POST['category_id'];
    $unit_id = (int) $_POST['unit_id'];
    $initial_stock = (int) $_POST['initial_stock'];
    $safety_stock = (int) $_POST['safety_stock'];
    $location_id = (int) $_POST['location_id'];
    $description = sanitizeInput($_POST['description']);

    // Validasi sederhana (bisa dikembangkan)
    if (empty($product_code) || empty($product_name)) {
        $error = "Kode dan nama produk wajib diisi.";
    } else {
        $sql = "INSERT INTO products (
                    product_code, product_name, category_id, unit_id, 
                    initial_stock, current_stock, safety_stock, location_id, description
                ) VALUES (
                    '$product_code', '$product_name', $category_id, $unit_id, 
                    $initial_stock, $initial_stock, $safety_stock, $location_id, '$description'
                )";

        if ($conn->query($sql)) {
            header("Location: list.php?success=1");
            exit();
        } else {
            $error = "Gagal menambahkan produk: " . $conn->error;
        }
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
                            <h2>Tambah Data Barang</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="product_code" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_code" name="product_code" required>
                                        <div class="invalid-feedback">Kode Barang wajib diisi.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="product_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                                        <div class="invalid-feedback">Nama Barang wajib diisi.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Kategori</label>
                                        <select class="form-select" id="category_id" name="category_id" aria-label="Kategori">
                                            <option value="" selected>Pilih Kategori</option>
                                            <?php while ($row = $categories->fetch_assoc()): ?>
                                                <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['category_name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="unit_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <select class="form-select" id="unit_id" name="unit_id" required aria-label="Satuan">
                                            <option value="" selected>Pilih Satuan</option>
                                            <?php while ($row = $units->fetch_assoc()): ?>
                                                <option value="<?= $row['unit_id'] ?>"><?= htmlspecialchars($row['unit_name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="invalid-feedback">Satuan wajib dipilih.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="initial_stock" class="form-label">Stok Awal <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="initial_stock" name="initial_stock" value="0" min="0" step="1" required>
                                        <div class="invalid-feedback">Stok Awal wajib diisi dan tidak boleh kurang dari 0.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="safety_stock" class="form-label">Safety Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="safety_stock" name="safety_stock" value="0" min="0" step="1" required>
                                        <div class="invalid-feedback">Safety Stock wajib diisi dan tidak boleh kurang dari 0.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="location_id" class="form-label">Lokasi Penyimpanan</label>
                                        <select class="form-select" id="location_id" name="location_id" aria-label="Lokasi Penyimpanan">
                                            <option value="" selected>Pilih Lokasi</option>
                                            <?php while ($row = $locations->fetch_assoc()): ?>
                                                <option value="<?= $row['location_id'] ?>"><?= htmlspecialchars($row['location_name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="description" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
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

        <script>
            (() => {
                'use strict';
                const forms = document.querySelectorAll('.needs-validation');
                Array.from(forms).forEach(form => {
                    form.addEventListener('submit', event => {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            })();
        </script>

</body>

</html>