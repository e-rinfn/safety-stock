<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';
include_once __DIR__ . '../../../config/config.php';

checkLogin();
// checkRole('Admin'); // Hanya admin yang bisa akses

// Query untuk mendapatkan daftar user dengan role
$sql = "SELECT u.user_id, u.username, u.full_name, u.email, u.is_active, r.role_name 
        FROM users u
        JOIN roles r ON u.role_id = r.role_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
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
                                                        <a href="edit.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                        <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                                            <a href="delete.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete()">Hapus</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data pengguna</td>
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

    <!-- Core JS -->
    <?php include __DIR__ . '../../../includes/footer.php'; ?>

    <script>
        // Fungsi konfirmasi penghapusan
        function confirmDelete() {
            return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');
        }

        // Inisialisasi komponen Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Inisialisasi alert yang bisa ditutup
            var alertList = [].slice.call(document.querySelectorAll('.alert'));
            alertList.forEach(function(alert) {
                new bootstrap.Alert(alert);
            });

            // Inisialisasi modal filter
            var filterModal = new bootstrap.Modal(document.getElementById('filterModal'), {
                keyboard: false
            });
        });
    </script>
</body>

</html>