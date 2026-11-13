<?php
require_once 'config.php';
try {
    $server = new PDO('mysql:host='.DB_HOST.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $server->exec("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo=db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS wallets (
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL UNIQUE,
        balance DECIMAL(15,2) NOT NULL DEFAULT 0, wallet_status ENUM('active','blocked') DEFAULT 'active',
        pin VARCHAR(10) NOT NULL DEFAULT '123456', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        type ENUM('topup','transfer_in','transfer_out','payment') NOT NULL,
        amount DECIMAL(15,2) NOT NULL, description VARCHAR(255) NOT NULL,
        reference_code VARCHAR(40) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_date(user_id,created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $count=(int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count===0) {
        $seed=$pdo->prepare("INSERT INTO users(name,email,password_hash) VALUES(?,?,?)");
        $hash=password_hash('koiny123',PASSWORD_DEFAULT);
        $seed->execute(['Arya Demo','arya@koiny.test',$hash]); $id1=(int)$pdo->lastInsertId();
        $seed->execute(['Nadia Demo','nadia@koiny.test',$hash]); $id2=(int)$pdo->lastInsertId();
        $w=$pdo->prepare("INSERT INTO wallets(user_id,balance,pin) VALUES(?,?,?)");
        $w->execute([$id1,250000,'123456']); $w->execute([$id2,100000,'123456']);
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
      id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(160) NOT NULL, category VARCHAR(80) NOT NULL,
      description TEXT NOT NULL, price DECIMAL(15,2) NOT NULL, stock INT NOT NULL DEFAULT 0,
      weight INT NOT NULL DEFAULT 500, location VARCHAR(100) NOT NULL DEFAULT 'Sukabumi',
      rating DECIMAL(2,1) NOT NULL DEFAULT 0, image_url TEXT, video_url TEXT NULL,
      variants VARCHAR(255) NOT NULL DEFAULT 'Standard', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS addresses (
      id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, recipient VARCHAR(100) NOT NULL,
      phone VARCHAR(30) NOT NULL, label VARCHAR(40) NOT NULL, address TEXT NOT NULL,
      city VARCHAR(100) NOT NULL, postal_code VARCHAR(12) NOT NULL, is_default TINYINT(1) DEFAULT 0,
      FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart_items (
      id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL,
      variant VARCHAR(100) NOT NULL DEFAULT 'Standard', quantity INT NOT NULL DEFAULT 1,
      UNIQUE KEY uq_cart(user_id,product_id,variant), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlists (
      id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL,
      UNIQUE KEY uq_wish(user_id,product_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS vouchers (
      id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(30) NOT NULL UNIQUE, discount_type ENUM('percent','fixed','shipping') NOT NULL,
      discount_value DECIMAL(12,2) NOT NULL, min_purchase DECIMAL(15,2) NOT NULL DEFAULT 0, active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
      id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, address_id INT NULL,
      subtotal DECIMAL(15,2) NOT NULL, shipping_cost DECIMAL(15,2) NOT NULL, discount DECIMAL(15,2) NOT NULL DEFAULT 0,
      total DECIMAL(15,2) NOT NULL, courier VARCHAR(40) NOT NULL, payment_method VARCHAR(40) NOT NULL,
      payment_status VARCHAR(30) NOT NULL DEFAULT 'paid', order_status VARCHAR(30) NOT NULL DEFAULT 'processing',
      voucher_code VARCHAR(30) NULL, tracking_number VARCHAR(50) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id)
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
      id BIGINT AUTO_INCREMENT PRIMARY KEY, order_id BIGINT NOT NULL, product_id INT NULL,
      product_name VARCHAR(160) NOT NULL, variant VARCHAR(100) NOT NULL, price DECIMAL(15,2) NOT NULL,
      quantity INT NOT NULL, FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
      id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL,
      order_id BIGINT NOT NULL, rating INT NOT NULL, comment TEXT NOT NULL, photo_url TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_review(user_id,product_id,order_id)
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS chats (
      id BIGINT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, sender ENUM('user','admin') NOT NULL,
      message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $admin=$pdo->prepare("SELECT id FROM users WHERE email=?"); $admin->execute(['admin@koiny.test']);
    if (!$admin->fetchColumn()) {
      $st=$pdo->prepare("INSERT INTO users(name,email,password_hash) VALUES(?,?,?)");
      $st->execute(['Admin KOINY','admin@koiny.test',password_hash('admin123',PASSWORD_DEFAULT)]);
      $adminId=(int)$pdo->lastInsertId(); $w=$pdo->prepare("INSERT INTO wallets(user_id,balance,pin) VALUES(?,0,'123456')"); $w->execute([$adminId]);
      // role column is added below for compatibility with databases created by the first version.
    }
    try { $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('customer','admin') NOT NULL DEFAULT 'customer'"); } catch(Throwable $ignored) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL UNIQUE"); } catch(Throwable $ignored) {}
    $pdo->exec("UPDATE users SET role='admin' WHERE email='admin@koiny.test'");
    if ((int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn()===0) {
      $products=[
       ['Kaos Basic Oversize','Fashion','Kaos katun combed nyaman untuk aktivitas harian. Tersedia beberapa warna dan ukuran.',89000,35,350,'Bandung','https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800','S,M,L,XL,Hitam,Putih'],
       ['Sneakers Daily Move','Fashion','Sneakers ringan dengan desain minimalis untuk kuliah dan jalan santai.',259000,18,900,'Jakarta','https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800','39,40,41,42,43'],
       ['Botol Minum Insulated','Perlengkapan','Botol stainless steel menjaga suhu minuman lebih lama.',119000,42,600,'Sukabumi','https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800','500ml,750ml,Biru,Pink'],
       ['Wireless Earbuds Lite','Elektronik','Earbuds bluetooth untuk belajar, meeting, dan mendengarkan musik.',189000,21,250,'Jakarta','https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=800','Putih,Hitam'],
       ['Backpack Campus','Fashion','Tas harian dengan kompartemen laptop dan bahan tahan percikan air.',175000,15,700,'Bandung','https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800','Hitam,Navy,Abu'],
       ['Desk Lamp Minimal','Rumah','Lampu meja LED untuk menemani belajar dan bekerja.',99000,28,500,'Sukabumi','https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=800','Putih,Hitam']
      ];
      $st=$pdo->prepare("INSERT INTO products(name,category,description,price,stock,weight,location,image_url,variants) VALUES(?,?,?,?,?,?,?,?,?)");
      foreach($products as $x)$st->execute($x);
    }
    $pdo->exec("INSERT IGNORE INTO vouchers(code,discount_type,discount_value,min_purchase) VALUES ('KOINY10','percent',10,50000),('HEMAT15','fixed',15000,150000),('ONGKIRKU','shipping',0,0)");

    echo '<!doctype html><meta charset="utf-8"><body style="font:16px Arial;background:#f5f6ff;padding:40px"><h1 style="color:#3157c8">KOINY berhasil disiapkan!</h1><p>Database dan tabel sudah dibuat.</p><p>Akun demo: <b>arya@koiny.test</b> / <b>koiny123</b></p><p>Akun penerima: <b>nadia@koiny.test</b> / <b>koiny123</b></p><p>Admin toko: <b>admin@koiny.test</b> / <b>admin123</b></p><p>Admin toko: <b>admin@koiny.test</b> / <b>admin123</b></p><a href="index.php">Buka KOINY</a><p style="color:#d34">Demi keamanan, hapus atau ubah akses setup.php setelah instalasi lokal.</p></body>';
} catch(Throwable $e) { http_response_code(500); echo 'Setup gagal: '.htmlspecialchars($e->getMessage()); }
