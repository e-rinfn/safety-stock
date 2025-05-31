<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php?error_msg=ID tidak ditemukan.");
    exit();
}

$unit_id = (int) sanitizeInput($_GET['id']);

// Ambil data satuan berdasarkan ID
$result = $conn->query("SELECT * FROM units WHERE unit_id = $unit_id");
if ($result->num_rows === 0) {
    header("Location: list.php?error_msg=Satuan tidak ditemukan.");
    exit();
}
$unit = $result->fetch_assoc();

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_name = sanitizeInput($_POST['unit_name']);
    $unit_symbol = sanitizeInput($_POST['unit_symbol']);

    $sql = "UPDATE units SET unit_name = '$unit_name', unit_symbol = '$unit_symbol' WHERE unit_id = $unit_id";

    if ($conn->query($sql)) {
        header("Location: list.php?success_msg=Satuan berhasil diperbarui.");
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
                            <h2>Edit Data Satuan Produk</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label for="unit_name" class="form-label">Nama Satuan</label>
                                    <input type="text" class="form-control" id="unit_name" name="unit_name"
                                        value="<?= htmlspecialchars($unit['unit_name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="unit_symbol" class="form-label">Simbol</label>
                                    <input type="text" class="form-control" id="unit_symbol" name="unit_symbol"
                                        value="<?= htmlspecialchars($unit['unit_symbol']) ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
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