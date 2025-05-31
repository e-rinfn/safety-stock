<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
</head>

<body>
    <header>
        <h1>Inventory Management System</h1>
        <nav>
            <?php if (isLoggedIn()): ?>
                <a href="/safety-stock6/index.php">Dashboard</a>
                <a href="/safety-stock6/modules/products/list.php">Products</a>
                <a href="/safety-stock6/modules/incoming/list.php">Barang Masuk</a>
                <a href="/safety-stock6/modules/outgoing/list.php">Barang Keluar</a>
                <?php if ($_SESSION['role'] === 'owner'): ?>
                    <a href="/safety-stock6/modules/reports/stock.php">Reports</a>
                <?php endif; ?>
                <a href="/safety-stock6/modules/categories/list.php">Kategori</a>
                <a href="/safety-stock6/modules/units/list.php">Satuan</a>
                <a href="/safety-stock6/modules/locations/list.php">Lokasi</a>
                <a href="/safety-stock6/modules/supplier/list.php">Supplier</a>
                <a href="/safety-stock6/modules/auth/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>