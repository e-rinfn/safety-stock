<?php

$current_uri = $_SERVER['REQUEST_URI'];

function isActive($path)
{
    global $current_uri;
    return strpos($current_uri, $path) !== false ? 'active' : '';
}

?>

<!-- Menu -->

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <div class="app-brand demo">
                <a href="<?= $base_url ?>/index.php" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        <img src="<?= $base_url ?>/assets/img/Logo.png" alt="Logo" width="50" height="50">
                    </span>
                    <span class="menu-text fw-medium fs-6 ms-2">Inventory Management</span>
                </a>

                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                    <i class="bx bx-chevron-left bx-sm align-middle"></i>
                </a>
            </div>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item <?= isActive('/index.php') ?>">
            <a href="<?= $base_url ?>/index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <!-- Layouts -->


        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Halaman</span>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Master Data</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item <?= isActive('/products') ?>">
                    <a href="<?= $base_url ?>/modules/products/list.php" class="menu-link">
                        <div data-i18n="Without menu">Produk</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/categories') ?>">
                    <a href="<?= $base_url ?>/modules/categories/list.php" class="menu-link">
                        <div data-i18n="Without navbar">Kategori</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/units') ?>">
                    <a href="<?= $base_url ?>/modules/units/list.php" class="menu-link">
                        <div data-i18n="Container">Satuan</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/locations') ?>">
                    <a href="<?= $base_url ?>/modules/locations/list.php" class="menu-link">
                        <div data-i18n="Fluid">Lokasi</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/supplier') ?>">
                    <a href="<?= $base_url ?>/modules/supplier/list.php" class="menu-link">
                        <div data-i18n="Blank">Supplier</div>
                    </a>
                </li>
            </ul>
        </li>
        <!-- Components -->

        <li class="menu-item <?= isActive('/stock') ?>">
            <a href="<?= $base_url ?>/modules/stock/list.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-box"></i>
                <div data-i18n="Boxicons">Stock Barang</div>
            </a>
        </li>
        <li class="menu-item <?= isActive('/incoming') ?>">
            <a href="<?= $base_url ?>/modules/incoming/list.php" class="menu-link">
                <i class="menu-icon tf-icons bx  bx-log-in"></i>
                <div data-i18n="Boxicons">Barang Masuk</div>
            </a>
        </li>
        <li class="menu-item <?= isActive('/outgoing') ?>">
            <a href="<?= $base_url ?>/modules/outgoing/list.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-log-out"></i>
                <div data-i18n="Boxicons">Barang Keluar</div>
            </a>
        </li>

        <!-- Misc -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Laporan</span></li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Layouts">Laporan</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item <?= isActive('/reports/stock.php') ?>">
                    <a href="<?= $base_url ?>/modules/reports/stock.php" class="menu-link">
                        <div data-i18n="Without menu">Stok Barang</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/reports/incoming.php') ?>">
                    <a href="<?= $base_url ?>/modules/reports/incoming.php" class="menu-link">
                        <div data-i18n="Without navbar">Barang Masuk</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/reports/outgoing.php') ?>">
                    <a href="<?= $base_url ?>/modules/reports/outgoing.php" class="menu-link">
                        <div data-i18n="Container">Barang Keluar</div>
                    </a>
                </li>
                <li class="menu-item <?= isActive('/reports/safety_stock.php') ?>">
                    <a href="<?= $base_url ?>/modules/reports/safety_stock.php" class="menu-link">
                        <div data-i18n="Fluid">Baarang Kurang</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="text-center menu-header small text-uppercase"><span class="menu-header-text">=====|=====</span></li>

    </ul>
</aside>
<!-- / Menu -->