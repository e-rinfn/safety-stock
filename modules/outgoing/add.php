<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get products and outgoing types
$products = $conn->query("SELECT * FROM products WHERE is_active = 1 AND current_stock > 0");
$types = $conn->query("SELECT * FROM outgoing_types");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = sanitizeInput($_POST['product_id']);
    $quantity = sanitizeInput($_POST['quantity']);
    $type_id = sanitizeInput($_POST['type_id']);
    $destination = sanitizeInput($_POST['destination']);
    $notes = sanitizeInput($_POST['notes']);

    // Check available stock
    $product = $conn->query("SELECT current_stock FROM products WHERE product_id = $product_id")->fetch_assoc();
    if ($product['current_stock'] < $quantity) {
        $error = "Stok tidak mencukupi! Stok tersedia: " . $product['current_stock'];
    } else {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert outgoing record
            $sql = "INSERT INTO product_outgoing (product_id, quantity, type_id, destination, notes, user_id)
                    VALUES ($product_id, $quantity, $type_id, '$destination', '$notes', {$_SESSION['user_id']})";
            $conn->query($sql);

            // Update product stock
            $conn->query("UPDATE products SET current_stock = current_stock - $quantity WHERE product_id = $product_id");

            $conn->commit();
            header("Location: list.php?success=1");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
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
                            <h2>Tambah Barang Keluar</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Tanggal</label>
                                        <input type="datetime-local" name="transaction_date" class="form-control"
                                            value="<?php echo date('Y-m-d\TH:i'); ?>">
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Barang</label>
                                        <select name="product_id" class="form-select" required>
                                            <option value="">Pilih Barang</option>
                                            <?php while ($row = $products->fetch_assoc()): ?>
                                                <option value="<?php echo $row['product_id']; ?>">
                                                    <?php echo $row['product_name']; ?> (Stok: <?php echo $row['current_stock']; ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Jumlah</label>
                                        <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Jenis Tujuan</label>
                                        <select name="type_id" class="form-select" required>
                                            <?php while ($row = $types->fetch_assoc()): ?>
                                                <option value="<?php echo $row['type_id']; ?>"><?php echo $row['type_name']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Keterangan Tujuan</label>
                                        <input type="text" name="destination" class="form-control">
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Catatan</label>
                                        <textarea name="notes" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
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

</body>

</html>