<?php
include "config.php";

$error = "";

if(isset($_POST['register'])){
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if(mysqli_num_rows($check) > 0){
        $error = "Username sudah digunakan!";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
        header("Location: login.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register - RiceGuard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body {
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)),
    url('https://images.unsplash.com/photo-1592982537447-7440770cbfc9') center/cover no-repeat;
}

.card {
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(20px);
    padding:45px 40px;
    border-radius:25px;
    width:370px;
    text-align:center;
    color:white;
    box-shadow:
        0 20px 50px rgba(0,0,0,0.5),
        0 0 40px rgba(46,125,50,0.4);
    border:1px solid rgba(255,255,255,0.2);
    animation:floatCard 3s ease-in-out infinite alternate;
}

@keyframes floatCard {
    from { transform:translateY(0px); }
    to { transform:translateY(-10px); }
}

.card h2 {
    margin-bottom:25px;
    font-weight:600;
}

input {
    width:100%;
    padding:14px;
    margin:12px 0;
    border:none;
    border-radius:12px;
    background:rgba(255,255,255,0.2);
    color:white;
    font-size:14px;
    outline:none;
    transition:0.3s;
}

input::placeholder {
    color:rgba(255,255,255,0.7);
}

input:focus {
    background:rgba(255,255,255,0.3);
    box-shadow:0 0 10px rgba(251,192,45,0.6);
}

button {
    width:100%;
    padding:14px;
    border:none;
    border-radius:30px;
    background:linear-gradient(135deg,#fbc02d,#f57c00);
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    color:white;
    font-size:15px;
    margin-top:10px;
    box-shadow:0 8px 20px rgba(0,0,0,0.4);
}

button:hover {
    transform:translateY(-3px);
    box-shadow:0 12px 25px rgba(0,0,0,0.5);
}

.error {
    background:rgba(255,82,82,0.9);
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
    font-size:14px;
}

a {
    color:#fbc02d;
    text-decoration:none;
    font-size:14px;
    transition:0.3s;
}

a:hover {
    text-decoration:underline;
}

.back-btn {
    display:block;
    margin-top:15px;
    padding:12px;
    border-radius:30px;
    background:rgba(255,255,255,0.15);
    color:white;
    text-decoration:none;
    font-size:14px;
    transition:0.3s;
    border:1px solid rgba(255,255,255,0.2);
}

.back-btn:hover {
    background:rgba(255,255,255,0.25);
}
</style>
</head>

<body>

<div class="card">
    <h2>🌾 RiceGuard Register</h2>

    <?php if($error != ""): ?>
        <div class="error"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Daftar</button>
        <a href="index.php" class="back-btn">Halaman Beranda</a>
    </form>

    <br>
    <a href="login.php">Sudah punya akun? Login</a>
</div>

</body>
</html>