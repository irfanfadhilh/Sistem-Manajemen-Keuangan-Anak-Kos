<?php
include 'koneksi.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    $check = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if(mysqli_num_rows($check) > 0){
        $message = "Username sudah terdaftar! Pilih yang lain.";
    } else {
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        if (mysqli_query($koneksi, $sql)) {
            header("Location: login.php"); 
            exit();
        } else {
            $message = "Gagal mendaftar! Coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Keuangan Kos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at 50% 0%, #1e293b 0%, #0f172a 40%, #020617 100%);
            height: 100vh;
            overflow: hidden;
            color: #e2e8f0;
        }

     
        .ambient-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

    
        .glass-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
        }

      
        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: white;
            transition: all 0.3s ease;
        }
        .glass-input:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #06b6d4; 
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2);
            outline: none;
        }
        .glass-input::placeholder { color: #64748b; }

        .btn-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }
        .btn-cyan:hover {
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="flex items-center justify-center">

    <div class="ambient-glow"></div>

    <div class="w-full max-w-sm px-6">
        
        <div class="glass-card rounded-2xl p-8 w-full">
            
            <div class="text-center mb-8">
                <div class="mx-auto h-12 w-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-2xl mb-4 shadow-[0_0_15px_rgba(6,182,212,0.3)]">
                    🚀
                </div>
                <h2 class="text-2xl font-semibold text-white tracking-tight">Buat Akun Baru</h2>
                <p class="text-xs text-slate-400 mt-2">Mulai atur keuanganmu dengan lebih baik.</p>
            </div>
            
            <?php if($message): ?>
                <div class="mb-6 text-xs text-red-300 bg-red-900/30 border border-red-500/30 p-3 rounded-lg text-center">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 ml-1">Username</label>
                    <input type="text" name="username" placeholder="Buat username unik" required 
                           class="glass-input w-full px-4 py-3 rounded-xl text-sm">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5 ml-1">Password</label>
                    <input type="password" name="password" placeholder="••••••••" required 
                           class="glass-input w-full px-4 py-3 rounded-xl text-sm">
                </div>

                <button type="submit" class="btn-cyan w-full text-white font-semibold py-3 rounded-xl transition-all duration-300 mt-2">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <p class="text-xs text-slate-500">
                    Sudah punya akun? 
                    <a href="login.php" class="text-cyan-400 hover:text-cyan-300 font-medium transition ml-1 hover:underline">Login disini</a>
                </p>
            </div>

        </div>
    </div>
</body>
</html>