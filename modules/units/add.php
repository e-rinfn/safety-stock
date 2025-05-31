<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_name = sanitizeInput($_POST['unit_name']);
    $unit_symbol = sanitizeInput($_POST['unit_symbol']);

    $sql = "INSERT INTO units (unit_name, unit_symbol) VALUES ('$unit_name', '$unit_symbol')";

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
                            <h2>Tambah Data Satuan Produk</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label for="unit_name" class="form-label">Nama Satuan</label>
                                    <input type="text" class="form-control" id="unit_name" name="unit_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="unit_symbol" class="form-label">Simbol</label>
                                    <input type="text" class="form-control" id="unit_symbol" name="unit_symbol" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>

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