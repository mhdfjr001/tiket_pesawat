<?php
session_start();
require "config/koneksi.php";

$error = "";

if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f1f5f9;
        margin: 0;
    }

    .navbar {
        background: #0f172a;
        color: white;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
    }

    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 90vh;
    }

    .box {
        background: white;
        padding: 30px;
        border-radius: 11px;
        width: 350px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .box h3 {
        margin-bottom: 20px;
        text-align: center;
        color: #0f172a;
    }

    input {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #0f172a;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
    }

    button:hover {
        opacity: 0.9;
    }

    .error {
        background: #ef4444;
        color: white;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .link {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }

    .link a {
        color: #0f172a;
        text-decoration: none;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="navbar">
    <div><b>✈️ SISTEM TIKET PESAWAT</b></div>
</div>

<div class="container">
    <div class="box">
        <h3>Login</h3>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button name="login">Masuk</button>
        </form>
        <div class="link">
            Belum punya akun? <a href="register.php">Register</a>
        </div>
    </div>
</div>

</body>
</html>