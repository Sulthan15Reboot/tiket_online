<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// =======================================
// DATA HARGA TIKET
// =======================================
$harga = [
    'GRD' => ['nama' => 'Garuda',       'Eksekutif' => 1500000, 'Bisnis' => 900000, 'Ekonomi' => 500000],
    'MPT' => ['nama' => 'Merpati',      'Eksekutif' => 1200000, 'Bisnis' => 800000, 'Ekonomi' => 400000],
    'BTV' => ['nama' => 'Batavia',      'Eksekutif' => 1000000, 'Bisnis' => 700000, 'Ekonomi' => 300000]
];

$result = null;
$success = false;
$error = "";

if (isset($_POST['simpan'])) {
    $nama       = trim($_POST['nama']);
    $kode       = $_POST['kode'];
    $kelas      = $_POST['kelas'];
    $jumlah     = (int)$_POST['jumlah'];

    if (empty($nama)) {
        $error = "Nama pemesan tidak boleh kosong!";
    } else {
        $namaPesawat = $harga[$kode]['nama'];
        $hargaTiket  = $harga[$kode][$kelas];
        $totalBayar  = $hargaTiket * $jumlah;

        // Simpan ke database
        $stmt = $conn->prepare("INSERT INTO pemesanan 
            (user_id, nama_pemesan, kode_pesawat, nama_pesawat, kelas, harga_tiket, jumlah_tiket, total_bayar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("issssiii", 
            $_SESSION['user_id'], 
            $nama, 
            $kode, 
            $namaPesawat, 
            $kelas, 
            $hargaTiket, 
            $jumlah, 
            $totalBayar
        );

        if ($stmt->execute()) {
            $success = true;
            $result = [
                'nama'        => $nama,
                'namaPesawat' => $namaPesawat,
                'kelas'       => $kelas,
                'harga'       => $hargaTiket,
                'jumlah'      => $jumlah,
                'total'       => $totalBayar
            ];
        } else {
            $error = "Gagal menyimpan pemesanan: " . $conn->error;
        }
    }
}

// Ambil riwayat pemesanan user
$stmt = $conn->prepare("SELECT * FROM pemesanan WHERE user_id = ? ORDER BY tanggal_pemesanan DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$riwayat = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Online Jakarta - Malaysia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .box {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 10px;
            text-align: left;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #28a745;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 15px;
        }
        .error { color: red; text-align: center; }
        .success { color: green; text-align: center; font-weight: bold; }
        .logout {
            text-align: right;
            margin-bottom: 10px;
        }
        .user-info {
            text-align: right;
            color: #555;
            font-size: 14px;
        }
        .riwayat th {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- User Info & Logout -->
    <div class="box">
        <div class="logout">
            <div class="user-info">
                Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! 
                <a href="logout.php" style="color: #dc3545; margin-left: 15px;">Logout</a>
            </div>
        </div>
        <h2>TIKET ONLINE JAKARTA → MALAYSIA</h2>
    </div>

    <!-- Form Pemesanan -->
    <div class="box">
        <h3>Form Pemesanan Tiket</h3>
        
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success">✅ Pemesanan berhasil disimpan!</p>
        <?php endif; ?>

        <form method="post">
            <table>
                <tr>
                    <td>Nama Pemesan</td>
                    <td><input type="text" name="nama" required></td>
                </tr>
                <tr>
                    <td>Kode Pesawat</td>
                    <td>
                        <select name="kode" required>
                            <option value="GRD">GRD - Garuda</option>
                            <option value="MPT">MPT - Merpati</option>
                            <option value="BTV">BTV - Batavia</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>
                        <label><input type="radio" name="kelas" value="Eksekutif" checked> Eksekutif</label><br>
                        <label><input type="radio" name="kelas" value="Bisnis"> Bisnis</label><br>
                        <label><input type="radio" name="kelas" value="Ekonomi"> Ekonomi</label>
                    </td>
                </tr>
                <tr>
                    <td>Jumlah Tiket</td>
                    <td>
                        <select name="jumlah" required>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <button type="submit" name="simpan">PESAN SEKARANG</button>
                        <button type="reset">BATAL</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <!-- Detail Pemesanan Terakhir -->
    <?php if ($result): ?>
    <div class="box">
        <h3>DETAIL PEMESANAN TERAKHIR</h3>
        <div class="result">
            <?php
            function line($label, $value) {
                echo "<strong>$label :</strong> $value<br>";
            }
            line('Nama Pemesan', htmlspecialchars($result['nama']));
            line('Pesawat', $result['namaPesawat']);
            line('Kelas', $result['kelas']);
            line('Harga per Tiket', 'Rp ' . number_format($result['harga'], 0, ',', '.'));
            line('Jumlah Tiket', $result['jumlah']);
            line('Total Bayar', '<strong>Rp ' . number_format($result['total'], 0, ',', '.') . '</strong>');
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Riwayat Pemesanan -->
    <div class="box">
        <h3>Riwayat Pemesanan Anda</h3>
        
        <?php if ($riwayat->num_rows > 0): ?>
        <table class="riwayat">
            <tr>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Pesawat</th>
                <th>Kelas</th>
                <th>Jumlah</th>
                <th>Total Bayar</th>
            </tr>
            <?php while ($row = $riwayat->fetch_assoc()): ?>
            <tr>
                <td><?php echo date('d M Y H:i', strtotime($row['tanggal_pemesanan'])); ?></td>
                <td><?php echo htmlspecialchars($row['nama_pemesan']); ?></td>
                <td><?php echo $row['nama_pesawat']; ?> (<?php echo $row['kode_pesawat']; ?>)</td>
                <td><?php echo $row['kelas']; ?></td>
                <td align="center"><?php echo $row['jumlah_tiket']; ?></td>
                <td align="right">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p style="text-align:center; color:#666;">Belum ada pemesanan.</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>