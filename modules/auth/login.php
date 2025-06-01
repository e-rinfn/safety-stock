<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    $sql = "SELECT user_id, username, password, role_name as role FROM users 
            JOIN roles ON users.role_id = roles.role_id 
            WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Pastikan $base_url didefinisikan sebelumnya
            header("Location: {$base_url}/index.php");
            exit();
        }
    }

    $error = "Invalid username or password";
}

?>

<?php include '../../includes/head.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <!-- Logo -->
        <div class="text-center mb-3">
            <img src="<?= $base_url ?>/assets/img/Logo.png" alt="Logo" style="max-width: 120px;" class="img-fluid">
        </div>

        <h2 class="mb-4 text-center">Login</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
                <div class="invalid-feedback">
                    Username wajib diisi.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="invalid-feedback">
                    Password wajib diisi.
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

<script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>

<?php include '../../includes/footer.php'; ?>