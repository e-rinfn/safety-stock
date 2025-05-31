<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['supplier_name'];
    $contact = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $contact, $phone, $email, $address, $is_active);
    $stmt->execute();

    header("Location: list.php");
    exit;
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
                            <h2>Tambah Data Supplier</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Nama Supplier</label>
                                    <input type="text" name="supplier_name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="address" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        Aktif
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-success">Simpan</button>
                                <a href="list.php" class="btn btn-secondary">Kembali</a>
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