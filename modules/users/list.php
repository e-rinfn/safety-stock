<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();
// checkRole('Admin'); // Hanya admin yang bisa akses

// Query untuk mendapatkan daftar user dengan role
$sql = "SELECT u.user_id, u.username, u.full_name, u.email, u.is_active, r.role_name 
        FROM users u
        JOIN roles r ON u.role_id = r.role_id";
$result = $conn->query($sql);
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
                            <h2>Daftar Pengguna</h2>
                            <div>
                                <a href="add.php" class="btn btn-primary me-2">Tambah Pengguna</a>
                            </div>
                        </div>



                        <div class="card p-3">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Data berhasil disimpan!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>



                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Username</th>
                                            <th>Nama Lengkap</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php $no = 1; ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['role_name']); ?></td>
                                                    <td><?php echo $row['is_active'] ? 'Aktif' : 'Nonaktif'; ?></td>
                                                    <td>
                                                        <a href="edit.php?id=<?php echo $row['user_id']; ?>">Edit</a>
                                                        <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                                            | <a href="delete.php?id=<?php echo $row['user_id']; ?>" onclick="return confirm('Yakin hapus pengguna ini?')">Hapus</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7">Tidak ada data pengguna</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- / Isi Utama -->
                    </div>
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

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="get" action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filter Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category_id">
                                <option value="">Semua Kategori</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($category_id == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Penyimpanan</label>
                            <select class="form-select" name="location_id">
                                <option value="">Semua Lokasi</option>
                                <?php while ($location = $locations->fetch_assoc()): ?>
                                    <option value="<?php echo $location['location_id']; ?>" <?php echo ($location_id == $location['location_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location['location_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kondisi Stok</label>
                            <select class="form-select" name="stock_condition">
                                <option value="">Semua Kondisi</option>
                                <option value="low" <?php echo ($stock_condition == 'low') ? 'selected' : ''; ?>>Stok Rendah (di bawah stok minimal)</option>
                                <option value="adequate" <?php echo ($stock_condition == 'adequate') ? 'selected' : ''; ?>>Stok Cukup</option>
                                <option value="critical" <?php echo ($stock_condition == 'critical') ? 'selected' : ''; ?>>Stok Kritis (≤50% stok minimal)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="<?= $base_url ?>/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= $base_url ?>/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= $base_url ?>/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= $base_url ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="<?= $base_url ?>/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="<?= $base_url ?>/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="<?= $base_url ?>/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="<?= $base_url ?>/assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>