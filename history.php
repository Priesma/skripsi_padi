<?php
include "config.php"; // WAJIB supaya session_start() aktif

// Cek login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Jika klik tandai sudah
if(isset($_GET['done'])){
    $id = intval($_GET['done']);
    mysqli_query($conn, "UPDATE detections 
                         SET status='sudah ditangani' 
                         WHERE id='$id' AND user_id='$user_id'");
}

// Ambil data histori user
$result = mysqli_query($conn, "SELECT * FROM detections 
                               WHERE user_id='$user_id'
                               ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>History Deteksi - RiceGuard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
/* CSS kamu tetap, tidak saya ubah */
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }

body {
    min-height:100vh;
    padding:60px 8%;
    background:
    linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)),
    url('https://images.unsplash.com/photo-1592982537447-7440770cbfc9') center/cover no-repeat;
    color:white;
}

.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:40px;
}

.back-btn {
    background:rgba(255,255,255,0.15);
    padding:10px 20px;
    border-radius:25px;
    text-decoration:none;
    color:white;
}

.history-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
    gap:25px;
}

.history-card {
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(20px);
    padding:20px;
    border-radius:20px;
}

.history-card img {
    width:100%;
    border-radius:15px;
    margin-bottom:15px;
}

.date {
    font-size:13px;
    opacity:0.8;
    margin-bottom:10px;
}

.status {
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    font-size:13px;
    margin-bottom:10px;
}

.status.done { background:#2e7d32; }
.status.pending { background:#f57c00; }

.btn {
    display:inline-block;
    padding:8px 15px;
    background:linear-gradient(135deg,#fbc02d,#f57c00);
    border-radius:20px;
    text-decoration:none;
    color:white;
    font-size:13px;
}
</style>
</head>
<body>

<div class="header">
    <h2>History Deteksi - <?= $_SESSION['username']; ?></h2>
    <a href="index.php" class="back-btn">← Kembali</a>
</div>

<div class="history-grid">

<?php if(mysqli_num_rows($result) > 0): ?>
<?php while($row = mysqli_fetch_assoc($result)): ?>

    <div class="history-card">
        <img src="<?= $row['result_path']; ?>">

        <h4><?= ucfirst(str_replace("_"," ",$row['detected_class'])); ?></h4>

        <div class="date">
            <?= date('d M Y H:i', strtotime($row['created_at'])); ?>
        </div>

        <?php if(isset($row['status']) && $row['status'] == 'sudah ditangani'): ?>
            <span class="status done">Sudah Ditangani</span>
        <?php else: ?>
            <span class="status pending">Belum Ditangani</span>
        <?php endif; ?>

        <br>

        <?php if(!isset($row['status']) || $row['status'] != 'sudah ditangani'): ?>
            <a class="btn" href="history.php?done=<?= $row['id']; ?>">
                Tandai Sudah
            </a>
        <?php endif; ?>
    </div>

<?php endwhile; ?>
<?php else: ?>
    <p>Belum ada histori deteksi.</p>
<?php endif; ?>

</div>

</body>
</html>