<?php
ob_start();                    // Penting: cegah "headers already sent"
session_start();

require_once 'koneksi.php';

$error = '';
$success = '';
$activeTab = isset($_POST['register']) ? 'register' : 'login';

// ======================
// PROSES LOGIN
// ======================
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                header("Location: index.php");   // Ganti jadi index.php kalau nama filenya index.php
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}

// ======================
// PROSES REGISTER
// ======================
if (isset($_POST['register'])) {
    $reg_username = trim($_POST['reg_username']);
    $email        = trim($_POST['email']);
    $reg_password = $_POST['reg_password'];
    $confirm      = $_POST['confirm_password'];

    if (empty($reg_username) || empty($email) || empty($reg_password)) {
        $error = "Semua field wajib diisi!";
    } elseif ($reg_password !== $confirm) {
        $error = "Password dan konfirmasi tidak cocok!";
    } elseif (strlen($reg_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $hashed = password_hash($reg_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $reg_username, $hashed, $email);

        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Silakan login dengan akun baru Anda.";
            $activeTab = 'login';
        } else {
            $error = "Username atau email sudah digunakan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Tiket Online Jakarta Malaysia</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f4f6f9; 
            margin: 0; 
            padding: 20px;
        }
        .box { 
            width: 420px; 
            background: #fff; 
            border: 1px solid #ddd; 
            border-radius: 8px;
            padding: 25px; 
            margin: 40px auto; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h3 { text-align: center; margin-bottom: 20px; color: #333; }
        .tab { margin-bottom: 20px; text-align: center; }
        .tab button { 
            padding: 10px 25px; 
            margin: 0 5px; 
            border: 1px solid #ccc; 
            background: #f8f9fa; 
            cursor: pointer;
            border-radius: 4px 4px 0 0;
        }
        .tab button.active { 
            background: #fff; 
            border-bottom: 2px solid #007bff; 
            font-weight: bold;
        }
        .pane { display: none; }
        .pane.active { display: block; }
        table { width: 100%; }
        td { padding: 8px 0; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            width: 100%; 
            padding: 12px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            font-size: 16px;
            cursor: pointer;
        }
        button[type="submit"]:hover { background: #0056b3; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 13px; }
    </style>
</head>
<body>

<div class="box">
    <h3>TIKET ONLINE<br>JAKARTA - MALAYSIA</h3>
    
    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="tab">
        <button class="<?= $activeTab=='login' ? 'active' : '' ?>" onclick="showTab('login')">Login</button>
        <button class="<?= $activeTab=='register' ? 'active' : '' ?>" onclick="showTab('register')">Daftar</button>
    </div>
    
    <!-- Tab Login -->
    <div id="login" class="pane <?= $activeTab=='login' ? 'active' : '' ?>">
        <form method="post">
            <table>
                <tr><td>Username</td><td><input type="text" name="username" required autofocus></td></tr>
                <tr><td>Password</td><td><input type="password" name="password" required></td></tr>
                <tr><td colspan="2"><button type="submit" name="login">LOGIN</button></td></tr>
            </table>
        </form>
    </div>
    
    <!-- Tab Register -->
    <div id="register" class="pane <?= $activeTab=='register' ? 'active' : '' ?>">
        <form method="post">
            <table>
                <tr><td>Username</td><td><input type="text" name="reg_username" required></td></tr>
                <tr><td>Email</td><td><input type="email" name="email" required></td></tr>
                <tr><td>Password</td><td><input type="password" name="reg_password" required></td></tr>
                <tr><td>Konfirmasi</td><td><input type="password" name="confirm_password" required></td></tr>
                <tr><td colspan="2"><button type="submit" name="register">DAFTAR</button></td></tr>
            </table>
        </form>
    </div>
</div>

<div class="footer">&copy; 2026 Tiket Online Jakarta - Malaysia</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab button').forEach(b => b.classList.remove('active'));
    
    document.getElementById(tab).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>

</body>
</html>