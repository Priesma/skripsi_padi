<?php
include "config.php";

$logged_in = isset($_SESSION['user_id']);

// Kalau belum login
if(!isset($_SESSION['user_id'])){
    $logged_in = false;
} else {
    $logged_in = true;
}

$hasil_deteksi = [];
$result_image = "";

if(isset($_POST['detect']) && $logged_in){

    $image_name = time() . "_" . $_FILES['image']['name'];
    $upload_path = "uploads/" . $image_name;
    move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);

    // Jalankan Python YOLO
    $command = "python detect.py \"" . $upload_path . "\" 2>&1";
    $output = shell_exec($command);

    $data = json_decode($output, true);

    if(!empty($data)){

        $user_id = $_SESSION['user_id'];

        // Ambil gambar hasil deteksi dari output pertama
        $result_image = $data[0]['output'];

        /*
            Menyimpan hasil berdasarkan nama penyakit.
            Setiap penyakit akan menyimpan:
            - confidence tertinggi
            - total confidence
            - jumlah deteksi
            - rata-rata confidence
        */
        foreach($data as $item){

            $class = $item['class'];
            $conf = $item['confidence'];
            $output_img = $item['output'];

            // Jika penyakit belum ada, buat data awal
            if(!isset($hasil_deteksi[$class])){

                $hasil_deteksi[$class] = [
                    "class" => $class,
                    "confidence" => $conf,          // Confidence tertinggi
                    "total_confidence" => $conf,    // Total confidence
                    "jumlah_deteksi" => 1,          // Jumlah objek terdeteksi
                    "avg_confidence" => $conf,      // Rata-rata confidence
                    "output" => $output_img
                ];

            } else {

                // Tambahkan total confidence
                $hasil_deteksi[$class]['total_confidence'] += $conf;

                // Tambahkan jumlah deteksi
                $hasil_deteksi[$class]['jumlah_deteksi'] += 1;

                // Update confidence tertinggi
                if($conf > $hasil_deteksi[$class]['confidence']){
                    $hasil_deteksi[$class]['confidence'] = $conf;
                }
            }
        }

        // Hitung rata-rata confidence untuk setiap penyakit
        foreach($hasil_deteksi as $class => $hasil){
            $hasil_deteksi[$class]['avg_confidence'] =
                $hasil['total_confidence'] / $hasil['jumlah_deteksi'];
        }

        // Simpan setiap penyakit yang terdeteksi ke database
        // Yang disimpan adalah confidence tertinggi setiap penyakit
        foreach($hasil_deteksi as $hasil){

            $detected_class = $hasil['class'];
            $confidence = $hasil['confidence'];

            mysqli_query($conn, "INSERT INTO detections 
                (user_id, image_path, result_path, detected_class, confidence) 
                VALUES 
                ('$user_id', '$upload_path', '$result_image', '$detected_class', '$confidence')");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>RiceGuard AI</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body>

<div class="navbar">
    <div>🌾 RiceGuard AI</div>
    <div>
        <?php if($logged_in): ?>
            Halo, <?= $_SESSION['username']; ?> |
            <a href="history.php" style="color:white;">History</a> |
            <a href="logout.php" style="color:white;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="color:white;">Login</a>
        <?php endif; ?>
    </div>
</div>

<section class="hero">
<div>

<h1>Deteksi Penyakit Tanaman Padi</h1>

<div style="margin-top:40px; color:white; text-align:center; max-width:800px; margin-left:auto; margin-right:auto;">

    <div class="tentang">
        <p>
            <strong>RiceGuard AI</strong> merupakan sistem berbasis Deep Learning 
            menggunakan model YOLOv8 untuk mendeteksi penyakit daun padi 
            secara cepat, otomatis, dan akurat. 
            Sistem ini membantu petani dan peneliti dalam 
            mengidentifikasi penyakit lebih dini sehingga 
            penanganan dapat dilakukan dengan tepat.
        </p>
    </div>

    <div class="process-section">

        <div class="process-step">
            <div class="circle">1</div>
            <h3>Upload Gambar</h3>
            <p>Pengguna mengunggah gambar daun padi</p>
        </div>

        <div class="line"></div>

        <div class="process-step">
            <div class="circle">2</div>
            <h3>Proses YOLOv8</h3>
            <p>Model AI menganalisis penyakit daun</p>
        </div>

        <div class="line"></div>

        <div class="process-step">
            <div class="circle">3</div>
            <h3>Hasil Deteksi</h3>
            <p>Sistem menampilkan hasil penyakit</p>
        </div>

    </div>
</div>

<?php if(!$logged_in): ?>
    <a href="login.php">
        <button class="btn">Mulai Deteksi</button>
    </a>
<?php endif; ?>


<?php if($logged_in): ?>
<div id="uploadSection" class="upload-card">
    <form method="POST" enctype="multipart/form-data">

        <div class="file-upload">
            <input type="file" name="image" id="fileInput" accept="image/*" required hidden>

            <label for="fileInput" class="custom-file-btn">
                Upload Gambar
            </label>

            <span id="fileName">Belum ada gambar yang dipilih</span>

            <div class="preview-container" id="previewContainer" style="display:none;">
                <img id="imagePreview">
            </div>
        </div>

        <button name="detect" class="btn">Deteksi Sekarang</button>

        <div id="loadingBox" class="loading-box" style="display:none;">
            <div class="loading-text">🔍 Sedang menganalisis gambar...</div>
            <div class="loading-bar">
                <div class="loading-fill" id="loadingFill"></div>
            </div>
        </div>

    </form>
</div>
<?php endif; ?>


<?php if(!empty($hasil_deteksi)): ?>
<div class="result-card">

    <div class="result-left">
        <img src="<?= $result_image; ?>">
    </div>

    <div class="result-right">
        <h3>Hasil Deteksi</h3>

        <p style="margin-bottom:20px;">
            Sistem mendeteksi <?= count($hasil_deteksi); ?> jenis penyakit pada gambar.
        </p>

        <?php foreach($hasil_deteksi as $hasil): ?>

            <div class="disease-result-box">

                <div class="result-item">
                    <span class="badge">
                        <?= ucfirst(str_replace("_"," ",$hasil['class'])); ?>
                    </span>
                </div>

                <div class="result-item">
                    <span class="label">Confidence Tertinggi</span>
                    <span class="confidence-text">
                        <?= round($hasil['confidence'] * 100, 1); ?>%
                    </span>
                </div>

                <div class="confidence-bar">
                    <div class="confidence-fill" 
                         style="width: <?= $hasil['confidence'] * 100; ?>%;">
                    </div>
                </div>

                <div class="result-item" style="margin-top:15px;">
                    <span class="label">Rata-rata Confidence</span>
                    <span class="confidence-text">
                        <?= round($hasil['avg_confidence'] * 100, 1); ?>%
                    </span>
                </div>

                <div class="confidence-bar avg-bar">
                    <div class="confidence-fill avg-fill" 
                         style="width: <?= $hasil['avg_confidence'] * 100; ?>%;">
                    </div>
                </div>

                <div class="result-item" style="margin-top:15px;">
                    <span class="label">Jumlah Deteksi</span>
                    <span class="confidence-text">
                        <?= $hasil['jumlah_deteksi']; ?> objek
                    </span>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>
<?php endif; ?>

</div>
</section>

<script>

const fileInput = document.getElementById("fileInput");
const fileName = document.getElementById("fileName");
const previewContainer = document.getElementById("previewContainer");
const imagePreview = document.getElementById("imagePreview");

if(fileInput){
    fileInput.addEventListener("change", function(){
        if(this.files && this.files[0]){
            const file = this.files[0];
            fileName.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e){
                imagePreview.src = e.target.result;
                previewContainer.style.display = "block";
            }
            reader.readAsDataURL(file);
        }
    });
}

function scrollToUpload(){
    document.getElementById("uploadSection").scrollIntoView({
        behavior: "smooth"
    });
}

const form = document.querySelector("form");
const loadingBox = document.getElementById("loadingBox");
const loadingFill = document.getElementById("loadingFill");

if(form){
    form.addEventListener("submit", function(){

        loadingBox.style.display = "block";

        let progress = 0;

        const interval = setInterval(() => {
            progress += Math.random() * 10;

            if(progress >= 90){
                progress = 90;
            }

            loadingFill.style.width = progress + "%";
        }, 200);

    });
}

window.addEventListener("load", function(){
    if(loadingFill){
        loadingFill.style.width = "100%";
    }
});

</script>

</body>
</html>