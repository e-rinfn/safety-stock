<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$location_id = (int) sanitizeInput($_GET['id']);

// Ambil data lokasi dari database
$result = $conn->query("SELECT * FROM storage_locations WHERE location_id = $location_id");
if ($result->num_rows === 0) {
    header("Location: list.php?error=notfound");
    exit();
}
$location = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location_name = sanitizeInput($_POST['location_name']);
    $description = sanitizeInput($_POST['description']);

    $sql = "UPDATE storage_locations 
            SET location_name = '$location_name', description = '$description' 
            WHERE location_id = $location_id";

    if ($conn->query($sql)) {
        header("Location: list.php?success=updated");
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
                            <h2>Edit Data Lokasi</h2>
                        </div>
                        <div class="card p-3">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label for="location_name" class="form-label">Nama Lokasi</label>
                                    <input type="text" class="form-control" id="location_name" name="location_name"
                                        value="<?php echo htmlspecialchars($location['location_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($location['description']); ?></textarea>
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