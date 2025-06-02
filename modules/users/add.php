<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();
// checkRole('Admin');

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

<?php include __DIR__ . '../../../includes/header.php'; ?>

<h2>Tambah Pengguna Baru</h2>

<?php if ($error): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color: green;"><?php echo $success; ?></p>
<?php endif; ?>

<form method="post">
    <div>
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $_POST['username'] ?? ''; ?>" required>
    </div>

    <div>
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>

    <div>
        <label>Nama Lengkap:</label>
        <input type="text" name="full_name" value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
    </div>

    <div>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>">
    </div>

    <div>
        <label>Telepon:</label>
        <input type="text" name="phone" value="<?php echo $_POST['phone'] ?? ''; ?>">
    </div>

    <div>
        <label>Role:</label>
        <select name="role_id" required>
            <option value="">Pilih Role</option>
            <?php while ($role = $roles_result->fetch_assoc()): ?>
                <option value="<?php echo $role['role_id']; ?>" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($role['role_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label>
            <input type="checkbox" name="is_active" <?php echo isset($_POST['is_active']) ? 'checked' : 'checked'; ?>>
            Aktif
        </label>
    </div>

    <button type="submit">Simpan</button>
    <a href="list.php">Kembali</a>
</form>

<?php include __DIR__ . '../../../includes/footer.php'; ?>