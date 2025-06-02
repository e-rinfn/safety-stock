<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();
// checkRole('admin'); // Hanya admin yang bisa akses

// Query untuk mendapatkan daftar user dengan role
$sql = "SELECT u.user_id, u.username, u.full_name, u.email, u.is_active, r.role_name 
        FROM users u
        JOIN roles r ON u.role_id = r.role_id";
$result = $conn->query($sql);
?>

<?php include __DIR__ . '../../../includes/header.php'; ?>

<h2>Daftar Pengguna</h2>
<a href="add.php">Tambah Pengguna Baru</a>

<table border="1">
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

<?php include __DIR__ . '../../../includes/footer.php'; ?>