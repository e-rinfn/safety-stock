<?php
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

// Get active products and suppliers
$products = $conn->query("SELECT product_id, product_code, product_name, current_stock FROM products WHERE is_active = 1 ORDER BY product_name");
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM suppliers WHERE is_active = 1 ORDER BY supplier_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_date = sanitizeInput($_POST['transaction_date']);
    $product_id = sanitizeInput($_POST['product_id']);
    $quantity = (float)sanitizeInput($_POST['quantity']);
    $supplier_id = sanitizeInput($_POST['supplier_id']);
    $purchase_price = (float)sanitizeInput($_POST['purchase_price']);
    $notes = sanitizeInput($_POST['notes']);

    // Validasi input
    if ($quantity <= 0) {
        $error = "Jumlah harus lebih dari 0";
    } else {
        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // 1. Insert record barang masuk
            $insert_sql = "INSERT INTO product_incoming 
                          (transaction_date, product_id, quantity, supplier_id, purchase_price, notes, user_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param(
                "siidssi",
                $transaction_date,
                $product_id,
                $quantity,
                $supplier_id,
                $purchase_price,
                $notes,
                $_SESSION['user_id']
            );
            $stmt->execute();

            // 2. Update stok produk
            $update_sql = "UPDATE products SET current_stock = current_stock + ? WHERE product_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("di", $quantity, $product_id);
            $stmt->execute();

            // Commit transaksi
            $conn->commit();

            header("Location: list.php?success=1");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal menyimpan data: " . $e->getMessage();
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
                            <h2>Tambah Barang Masuk</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Tanggal Masuk</label>
                                        <input type="datetime-local" name="transaction_date" class="form-control"
                                            value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Barang</label>
                                        <select name="product_id" class="form-select" required>
                                            <option value="">-- Pilih Barang --</option>
                                            <?php while ($product = $products->fetch_assoc()): ?>
                                                <option value="<?php echo $product['product_id']; ?>">
                                                    <?php echo $product['product_name']; ?> (<?php echo $product['product_code']; ?>)
                                                    - Stok: <?php echo $product['current_stock']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Jumlah</label>
                                        <input type="number" name="quantity" min="0.01" step="0.01" required class="form-control">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Supplier</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">-- Pilih --</option>
                                            <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                                                <option value="<?php echo $supplier['supplier_id']; ?>">
                                                    <?php echo $supplier['supplier_name']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Harga Beli (Rp)</label>
                                        <input type="number" name="purchase_price" min="0" step="100" class="form-control">
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-10">
                                        <label class="form-label">Catatan</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Simpan</button>
                                    </div>
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