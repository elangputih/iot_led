<?php
include 'koneksi.php';

// 1. PROSES JIKA TOMBOL DI WEBSITE DIKLIK (Mengubah Status di DB via AJAX)
if (isset($_GET['toggle'])) {
    $current_status = (int)$_GET['toggle'];
    $new_status = ($current_status == 1) ? 0 : 1;
    
    $conn->query("UPDATE device_status SET status = $new_status WHERE device_name = 'esp32_led'");
    
    // Kembalikan status baru dalam format JSON untuk dibaca JavaScript
    echo json_encode(['status' => $new_status]);
    exit();
}

// 2. PROSES JIKA ESP32 MEMINTA DATA ATAU UNTUK REAL-TIME CEK
if (isset($_GET['get_status'])) {
    $result = $conn->query("SELECT status FROM device_status WHERE device_name = 'esp32_led'");
    $row = $result->fetch_assoc();
    echo $row['status']; // ESP32 hanya akan membaca angka ini (0 atau 1)
    exit();
}

// Mengambil data status awal saat halaman pertama kali dimuat
$result = $conn->query("SELECT status FROM device_status WHERE device_name = 'esp32_led'");
$row = $result->fetch_assoc();
$status_sekarang = $row['status'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol LED ESP32</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            text-align: center; 
            background-color: #bfbfc1; 
            color: #fff;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background-color: #2a2a35;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(125, 123, 123, 0.3);
            width: 320px;
        }
        h2 { margin-bottom: 30px; font-size: 22px; color: #e0e0e0; }
        .status-container { font-size: 18px; margin-bottom: 30px; color: #aaa; }
        
        /* Styling teks status */
        #status-text { font-weight: bold; transition: color 0.3s ease; }
        .status-on { color: #2ecc71; text-shadow: 0 0 10px rgba(46, 204, 113, 0.4); }
        .status-off { color: #e74c3c; text-shadow: 0 0 10px rgba(231, 76, 60, 0.4); }

        /* Desain Toggle Switch ala iOS */
        .switch {
            position: relative;
            display: inline-block;
            width: 80px;
            height: 44px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 36px; width: 36px;
            left: 4px; bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        input:checked + .slider { background-color: #2ecc71; }
        input:checked + .slider:before { transform: translateX(36px); }
    </style>
</head>
<body>

    <div class="card">
        <h2>Kontrol LED ESP32</h2>
        
        <div class="status-container">
            Status: <span id="status-text" class="<?php echo $status_sekarang ? 'status-on' : 'status-off'; ?>">
                <?php echo $status_sekarang ? 'NYALA' : 'MATI'; ?>
            </span>
        </div>

        <label class="switch">
            <input type="checkbox" id="ledToggle" data-status="<?php echo $status_sekarang; ?>" <?php echo $status_sekarang ? 'checked' : ''; ?>>
            <span class="slider"></span>
        </label>
    </div>

    <script>
        document.getElementById('ledToggle').addEventListener('change', function() {
            let currentStatus = this.getAttribute('data-status');
            let statusText = document.getElementById('status-text');
            
            // Nonaktifkan switch sementara proses kirim data berlangsung
            this.disabled = true;

            // Kirim data ke database di latar belakang (AJAX Fetch)
            fetch(`index.php?toggle=${currentStatus}`)
                .then(response => response.json())
                .then(data => {
                    // Update attribute status dan tampilan berdasarkan respon database
                    this.setAttribute('data-status', data.status);
                    
                    if (data.status === 1) {
                        statusText.innerText = "NYALA";
                        statusText.className = "status-on";
                        this.checked = true;
                    } else {
                        statusText.innerText = "MATI";
                        statusText.className = "status-off";
                        this.checked = false;
                    }
                })
                .catch(error => {
                    alert('Gagal mengubah status, periksa koneksi.');
                    // Kembalikan posisi switch jika gagal
                    this.checked = !this.checked;
                })
                .finally(() => {
                    // Aktifkan kembali switch
                    this.disabled = false;
                });
        });
    </script>

</body>
</html>