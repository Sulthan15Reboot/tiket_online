-- Buat Database
CREATE DATABASE IF NOT EXISTS `tiket_online` 
CHARACTER SET utf8 
COLLATE utf8_general_ci;

USE `tiket_online`;

-- Tabel users (untuk login)
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabel pemesanan tiket
CREATE TABLE IF NOT EXISTS `pemesanan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `nama_pemesan` VARCHAR(100) NOT NULL,
    `kode_pesawat` VARCHAR(10) NOT NULL,
    `nama_pesawat` VARCHAR(50) NOT NULL,
    `kelas` ENUM('Eksekutif', 'Bisnis', 'Ekonomi') NOT NULL,
    `harga_tiket` INT NOT NULL,
    `jumlah_tiket` INT NOT NULL,
    `total_bayar` INT NOT NULL,
    `tanggal_pemesanan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert data user contoh (username: admin, password: admin123)
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@tiketonline.com')
ON DUPLICATE KEY UPDATE username = username;

-- Insert data user contoh lain (username: shoukii, password: 123456)
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`) VALUES
('shoukii', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Shoukii', 'shoukii@email.com')
ON DUPLICATE KEY UPDATE username = username;