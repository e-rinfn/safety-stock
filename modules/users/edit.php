<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();
// checkRole('admin');

if (!isset($_GET['id'])) {
    redirect('list.php');
}

$user_id = (int)$_GET['id'];

// Ambil data user yang akan diedit
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);

if ($user_result->num_rows === 0) {
    redirect('list.php');
}

$user = $user_result->fetch_assoc();

// Ambil daftar role untuk dropdown
$roles_sql = "SELECT * FROM roles";
$roles_result = $conn->query($roles_sql);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $role_id = (int)$_POST['role_id'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Jika password diisi, update password
    $password_update = '';
    if (!empty($_POST['password'])) {
        $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_DEFAULT);
        $password_update = ", password = '$password'";
    }

    // Validasi username unik (kecuali untuk user ini)
    $check_sql = "SELECT user_id FROM users WHERE username = '$username' AND user_id != $user_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $error = 'Username sudah digunakan';
    } else {
        $update_sql = "UPDATE users SET 
                      username = '$username',
                      full_name = '$full_name',
                      email = '$email',
                      phone = '$phone',
                      role_id = $role_id,
                      is_active = $is_active
                      $password_update
                      WHERE user_id = $user_id";

        if ($conn->query($update_sql)) {
            $success = 'Data pengguna berhasil diperbarui';
            // Refresh data user
            $user_result = $conn->query($user_sql);
            $user = $user_result->fetch_assoc();
        } else {
            $error = 'Gagal memperbarui data: ' . $conn->error;
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
                            <h2>Edit Data Pengguna</h2>
                        </div>

                        <div class="card p-4">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label for="username" class="form-label">Username:</label>
                                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label for="password" class="form-label">Password (biarkan kosong jika tidak ingin mengubah):</label>
                                        <input type="password" id="password" name="password" class="form-control">
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label for="full_name" class="form-label">Nama Lengkap:</label>
                                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label for="email" class="form-label">Email:</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label for="phone" class="form-label">Telepon:</label>
                                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label for="role_id" class="form-label">Role:</label>
                                        <select id="role_id" name="role_id" class="form-select" required>
                                            <?php while ($role = $roles_result->fetch_assoc()): ?>
                                                <option value="<?php echo $role['role_id']; ?>" <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_active">
                                                Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    <a href="list.php" class="btn btn-secondary">Kembali</a>
                                </div>
                            </form>
                        </div>

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