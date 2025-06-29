<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();
checkRole('Admin'); // Hanya admin yang bisa akses

// Ambil daftar role untuk dropdown
$roles_sql = "SELECT * FROM roles";
$roles_result = $conn->query($roles_sql);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_DEFAULT);
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $role_id = (int)$_POST['role_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi username unik
    $check_sql = "SELECT user_id FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $error = 'Username sudah digunakan';
    } else {
        $insert_sql = "INSERT INTO users (username, password, full_name, email, phone, role_id, is_active)
                      VALUES ('$username', '$password', '$full_name', '$email', '$phone', $role_id, $is_active)";

        if ($conn->query($insert_sql)) {
            $success = 'Pengguna berhasil ditambahkan';
            $_POST = []; // Clear form
        } else {
            $error = 'Gagal menambahkan pengguna: ' . $conn->error;
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
                            <h2>Tambah Data Pengguna</h2>
                        </div>

                        <div class="card p-4">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username:</label>
                                        <input type="text" name="username" class="form-control" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password:</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Lengkap:</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email:</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Telepon:</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo $_POST['phone'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Role:</label>
                                        <select name="role_id" class="form-select" required>
                                            <option value="">Pilih Role</option>
                                            <?php while ($role = $roles_result->fetch_assoc()): ?>
                                                <option value="<?php echo $role['role_id']; ?>" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary me-2">Simpan</button>
                                    <a href="list.php" class="btn btn-secondary">Kembali</a>
                                </div>
                            </form>

                        </div>

                        <!-- / Isi Utama -->

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