<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$category_id = sanitizeInput($_GET['id']);
$category = $conn->query("SELECT * FROM categories WHERE category_id = $category_id")->fetch_assoc();

if (!$category) {
    header("Location: list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = sanitizeInput($_POST['category_name']);
    $description = sanitizeInput($_POST['description']);

    $sql = "UPDATE categories SET 
            category_name = '$category_name',
            description = '$description'
            WHERE category_id = $category_id";

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
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Nama Kategori</label>
                                    <input type="text" class="form-control" name="category_name" id="category_name"
                                        value="<?php echo $category['category_name']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" name="description" id="description" rows="4"><?php echo $category['description']; ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
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