<?php
session_start();
require "config/koneksi.php";

// hanya admin
if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

$msg = "";

// TAMBAH USER
if (isset($_POST['tambah'])) {

    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $username, $password, $role);
    mysqli_stmt_execute($stmt);

    $msg = "User berhasil ditambahkan!";
}

// HAPUS USER
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    $msg = "User berhasil dihapus!";
}

// UPDATE ROLE
if (isset($_POST['update_role'])) {

    $id   = $_POST['id'];
    $role = $_POST['role'];

    $stmt = mysqli_prepare($conn, "UPDATE users SET role=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $role, $id);
    mysqli_stmt_execute($stmt);

    $msg = "Role berhasil diubah!";
}

// AMBIL DATA USER
$data = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Kelola User</title>

<style>
body {
    font-family: Segoe UI;
    background:#f1f5f9;
    margin:0;
}

/* NAVBAR */
.navbar {
    background:#0f172a;
    color:white;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
}

.navbar a {
    color:white;
    text-decoration:none;
    margin-left:15px;
}

/* CONTAINER */
.container {
    padding:25px;
}

/* FORM */
.form-box {
    background:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;
}

input, select {
    padding:8px;
    margin:5px;
}

/* TABLE */
.table {
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:10px;
    overflow:hidden;
}

.table th {
    background:#0f172a;
    color:white;
}

.table th, .table td {
    padding:10px;
}

.table tr:nth-child(even) {
    background:#f8fafc;
}

/* BUTTON */
.btn {
    padding:5px 10px;
    border:none;
    border-radius:5px;
    color:white;
    cursor:pointer;
}

.btn-edit { background:orange; }
.btn-delete { background:red; }
.btn-save { background:green; }

/* ALERT */
.alert {
    background:#22c55e;
    color:white;
    padding:10px;
    border-radius:6px;
    margin-bottom:10px;
}
</style>

</head>

<body>

<div class="navbar">
    <div><b>ADMIN PANEL</b></div>
    <div>
        <a href="admin.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

<h2>Kelola User</h2>

<?php if ($msg) echo "<div class='alert'>$msg</div>"; ?>

<!-- TAMBAH USER -->
<div class="form-box">
<form method="post">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>

<button class="btn btn-save" name="tambah">Tambah</button>
</form>
</div>

<!-- TABEL USER -->
<table class="table">
<tr>
<th>ID</th>
<th>Username</th>
<th>Role</th>
<th>Aksi</th>
</tr>

<?php while($u=mysqli_fetch_assoc($data)) { ?>
<tr>

<td><?= $u['id'] ?></td>
<td><?= $u['username'] ?></td>

<td>
<form method="post" style="display:flex; gap:5px;">
<input type="hidden" name="id" value="<?= $u['id'] ?>">

<select name="role">
<option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
<option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
</select>

<button class="btn btn-edit" name="update_role">Ubah</button>
</form>
</td>

<td>
<a href="?hapus=<?= $u['id'] ?>" onclick="return confirm('Yakin hapus user ini?')">
<button class="btn btn-delete">Hapus</button>
</a>
</td>

</tr>
<?php } ?>

</table>

</div>

</body>
</html>