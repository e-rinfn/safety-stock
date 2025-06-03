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

<?php include __DIR__ . '../../../includes/header.php'; ?>

<h2>Edit Pengguna</h2>

<?php if ($error): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color: green;"><?php echo $success; ?></p>
<?php endif; ?>

<form method="post">
    <div>
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>

    <div>
        <label>Password (biarkan kosong jika tidak ingin mengubah):</label>
        <input type="password" name="password">
    </div>

    <div>
        <label>Nama Lengkap:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
    </div>

    <div>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
    </div>

    <div>
        <label>Telepon:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
    </div>

    <div>
        <label>Role:</label>
        <select name="role_id" required>
            <?php while ($role = $roles_result->fetch_assoc()): ?>
                <option value="<?php echo $role['role_id']; ?>" <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($role['role_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label>
            <input type="checkbox" name="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
            Aktif
        </label>
    </div>

    <button type="submit">Simpan Perubahan</button>
    <a href="list.php">Kembali</a>
</form>

<?php include __DIR__ . '../../../includes/footer.php'; ?>