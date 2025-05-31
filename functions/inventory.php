<?php

/**
 * Fungsi-fungsi khusus untuk manajemen inventory
 */

require_once __DIR__ . '/helpers.php';

/**
 * Mendapatkan daftar kategori produk
 * @return array - Daftar kategori
 */
function getProductCategories()
{
    global $conn;
    $categories = [];

    $sql = "SELECT * FROM categories ORDER BY category_name";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[$row['category_id'] = $row['category_name']];
        }
    }

    return $categories;
}

/**
 * Mendapatkan daftar satuan produk
 * @return array - Daftar satuan
 */
function getProductUnits()
{
    global $conn;
    $units = [];

    $sql = "SELECT * FROM units ORDER BY unit_name";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $units[$row['unit_id']] = $row['unit_name'] . " (" . $row['unit_symbol'] . ")";
        }
    }

    return $units;
}

/**
 * Mendapatkan daftar lokasi penyimpanan
 * @return array - Daftar lokasi
 */
function getStorageLocations()
{
    global $conn;
    $locations = [];

    $sql = "SELECT * FROM storage_locations WHERE is_active = 1 ORDER BY location_name";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $locations[$row['location_id']] = $row['location_name'];
        }
    }

    return $locations;
}

/**
 * Mendapatkan daftar supplier
 * @return array - Daftar supplier
 */
function getSuppliers()
{
    global $conn;
    $suppliers = [];

    $sql = "SELECT * FROM suppliers WHERE is_active = 1 ORDER BY supplier_name";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[$row['supplier_id']] = $row['supplier_name'];
        }
    }

    return $suppliers;
}

/**
 * Mengecek apakah kode barang sudah ada
 * @param string $product_code - Kode barang
 * @param int $exclude_id - ID product yang tidak perlu dicek (untuk update)
 * @return bool - True jika sudah ada
 */
function isProductCodeExists($product_code, $exclude_id = null)
{
    global $conn;

    $sql = "SELECT product_id FROM products WHERE product_code = ?";
    $params = [$product_code];

    if ($exclude_id) {
        $sql .= " AND product_id != ?";
        $params[] = $exclude_id;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows > 0;
}

/**
 * Update stok produk
 * @param int $product_id - ID produk
 * @param float $quantity - Jumlah perubahan (+/-)
 * @param string $transaction_type - Jenis transaksi (incoming/outgoing/adjustment)
 * @param int $transaction_id - ID transaksi terkait
 * @param int $user_id - ID user yang melakukan
 * @return bool - True jika berhasil
 */
function updateProductStock($product_id, $quantity, $transaction_type, $transaction_id, $user_id)
{
    global $conn;

    // Mulai transaction
    $conn->begin_transaction();

    try {
        // Dapatkan stok saat ini
        $sql = "SELECT current_stock FROM products WHERE product_id = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_stock = $result->fetch_assoc()['current_stock'];

        // Hitung stok baru
        $new_stock = $current_stock + $quantity;

        // Update stok di tabel products
        $sql = "UPDATE products SET current_stock = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $new_stock, $product_id);
        $stmt->execute();

        // Catat di history
        $sql = "INSERT INTO stock_history (
            product_id, transaction_type, transaction_id, 
            previous_stock, quantity_change, new_stock, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'isiiddi',
            $product_id,
            $transaction_type,
            $transaction_id,
            $current_stock,
            $quantity,
            $new_stock,
            $user_id
        );
        $stmt->execute();

        // Cek apakah stok di bawah safety stock
        checkSafetyStock($product_id);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating stock: " . $e->getMessage());
        return false;
    }
}

/**
 * Cek safety stock dan buat notifikasi jika perlu
 * @param int $product_id - ID produk
 */
function checkSafetyStock($product_id)
{
    global $conn;

    $sql = "SELECT product_id, current_stock, safety_stock 
            FROM products 
            WHERE product_id = ? AND current_stock < safety_stock";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Cek apakah sudah ada notifikasi yang belum dibaca
        $sql = "SELECT notification_id FROM stock_notifications 
                WHERE product_id = ? AND is_read = 0";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Buat notifikasi baru
            $sql = "INSERT INTO stock_notifications 
                    (product_id, current_stock, safety_stock) 
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'idd',
                $product['product_id'],
                $product['current_stock'],
                $product['safety_stock']
            );
            $stmt->execute();
        }
    }
}

/**
 * Generate kode barang otomatis
 * @param string $prefix - Prefix kode (misal: 'BRG')
 * @return string - Kode barang yang unik
 */
function generateProductCode($prefix = 'BRG')
{
    global $conn;

    do {
        $code = $prefix . date('Ym') . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $sql = "SELECT product_id FROM products WHERE product_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    return $code;
}

/**
 * Mendapatkan daftar produk dengan stok kritis
 * @return array - Daftar produk dengan stok di bawah safety stock
 */
function getCriticalStockProducts()
{
    global $conn;
    $products = [];

    $sql = "SELECT p.product_id, p.product_code, p.product_name, 
                   p.current_stock, p.safety_stock, u.unit_name
            FROM products p
            JOIN units u ON p.unit_id = u.unit_id
            WHERE p.current_stock < p.safety_stock AND p.is_active = 1
            ORDER BY (p.current_stock / p.safety_stock) ASC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    return $products;
}
