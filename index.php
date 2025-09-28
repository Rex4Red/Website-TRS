 <?php
session_start();

// Cek apakah mahasiswa sudah login
if (!isset($_SESSION['nim'])) {
    // Cek cookie
    if (isset($_COOKIE['nim'])) {
        $_SESSION['nim'] = $_COOKIE['nim'];
        // Ambil jurusan berdasarkan 3 digit NIM
        $nim_prefix = substr($_SESSION['nim'], 0, 3);
        if (array_key_exists($nim_prefix, $jurusan)) {
            $_SESSION['jurusan'] = $jurusan[$nim_prefix];
        } else {
            header("Location: login.php");
            exit();
        }
    } else {
        header("Location: login.php");
        exit();
    }
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curhat Kampus - Wadah Keresahan Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        @font-face {
            font-family: 'Poppins';
            src: url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: var(--dark);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .tag {
            transition: all 0.2s ease;
        }
        
        .tag:hover {
            transform: scale(1.05);
        }
        
        textarea {
            min-height: 120px;
            transition: all 0.3s ease;
        }
        
        textarea:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: white;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="min-h-screen">
   
    <?php

    
    // ============================================
    // KONEKSI DATABASE
    // ============================================
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'db_curhat_kampus';
    $conn = new mysqli($host, $user, $password, $database);
    
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    // Buat tabel jika belum ada
    $conn->query("CREATE TABLE IF NOT EXISTS concerns (
        id VARCHAR(100) PRIMARY KEY,
        nama VARCHAR(50) NOT NULL,
        judul VARCHAR(255) NOT NULL,
        isi TEXT NOT NULL,
        kategori VARCHAR(50) NOT NULL,
        fakultas VARCHAR(50) NOT NULL,
        waktu DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // ============================================
    // PROSES FORM INPUT
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
        $id = uniqid();
        $nama = 'Anonim_'.rand(1000,9999);
        $judul = $conn->real_escape_string($_POST['judul']);
        $isi = $conn->real_escape_string($_POST['isi']);
        $kategori = $conn->real_escape_string($_POST['kategori']);
        $fakultas = $conn->real_escape_string($_POST['fakultas']);
        
        $sql = "INSERT INTO concerns (id, nama, judul, isi, kategori, fakultas) 
                VALUES ('$id', '$nama', '$judul', '$isi', '$kategori', '$fakultas')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "<script>alert('Error: ".$conn->error."');</script>";
        }
    }
    
    // ============================================
    // PROSES PENCARIAN & FILTER
    // ============================================
    $search = $_GET['search'] ?? '';
    $kategori_filter = $_GET['kategori'] ?? 'semua';
    
    $sql = "SELECT * FROM concerns";
    if (!empty($search) || $kategori_filter != 'semua') {
        $sql .= " WHERE ";
        $conditions = [];
        if (!empty($search)) {
            $conditions[] = "(judul LIKE '%$search%' OR isi LIKE '%$search%' OR fakultas LIKE '%$search%')";
        }
        if ($kategori_filter != 'semua') {
            $conditions[] = "kategori = '$kategori_filter'";
        }
        $sql .= implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY waktu DESC";
    $result = $conn->query($sql);
    ?>

    
    <!-- Header dengan gradient modern -->
    <header class="gradient-bg text-white shadow-xl">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0 flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-full animate-float">
                        <i class="fas fa-comments text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Curhat Kampus</h1>
                        <p class="text-indigo-100 opacity-90">Wadah curahan hati mahasiswa</p>
                    </div>
                </div>
                <nav class="flex items-center space-x-6">
                    <a href="#" class="nav-link font-medium">Beranda</a>
                    <a href="#" class="nav-link font-medium">Tentang</a>
                    <a href="#" class="nav-link font-medium">FAQ</a>
                    <div class="flex items-center space-x-3">
                        <img src="https://placehold.co/40x40/png?text=👨‍🎓" 
                             alt="Profil mahasiswa" 
                             class="w-9 h-9 rounded-full border-2 border-white/30">
                        <span class="font-medium hidden md:inline">Mahasiswa</span>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-10 animate-fadeIn">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Suara Mahasiswa <span class="text-indigo-600">Matters</span></h2>
                    <p class="text-gray-600 mb-6">Platform aman untuk berbagi keluh kesah, kritik, dan saran tentang kehidupan kampusmu.</p>
                    <div class="flex space-x-3">
                        <a href="#form-curhat" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-all duration-300 transform hover:-translate-y-0.5 shadow-md">
                            <i class="fas fa-pen-alt mr-2"></i>Curhat Sekarang
                        </a>
                        <a href="#daftar-curhat" class="px-6 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors duration-300">
                            <i class="fas fa-book-open mr-2"></i>Lihat Curhatan
                        </a>
                    </div>
                </div>
                <div class="hidden md:flex items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-50 p-8">
                    <img src="https://placehold.co/500x300/png?text=📢+Suara+Mahasiswa" 
                         alt="Ilustrasi mahasiswa sedang berdiskusi di kampus dengan latar belakang gedung universitas" 
                         class="rounded-lg shadow-md w-full h-auto max-w-md">
                </div>
            </div>
        </div>

        <!-- Form Input dengan card modern -->
        <div id="form-curhat" class="bg-white rounded-2xl shadow-xl overflow-hidden mb-10 animate-fadeIn" style="animation-delay: 0.1s;">
            <div class="gradient-bg px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-pen-alt mr-3"></i>Bagikan Keresahanmu
                </h2>
            </div>
            <form method="POST" class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                        <select name="kategori" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all duration-300">
                            <option value="akademik">Akademik</option>
                            <option value="fasilitas">Fasilitas Kampus</option>
                            <option value="organisasi">Organisasi</option>
                            <option value="dosen">Masalah Dosen</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                        <select name="fakultas" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all duration-300">
                            <option value="FIK">Fakultas Ilmu Komputer</option>
                            <option value="FT">Fakultas Teknik</option>
                            <option value="FE">Fakultas Ekonomi</option>
                            <option value="FH">Fakultas Hukum</option>
                            <option value="FP">Fakultas Pertanian</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Judul</label>
                    <input type="text" name="judul" placeholder="Masukkan judul singkat" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all duration-300">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Isi Keresahan</label>
                    <textarea name="isi" rows="4" placeholder="Ceritakan apa yang mengganggumu..." 
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all duration-300"></textarea>
                </div>
                
                <button type="submit" name="submit" 
                        class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white font-medium rounded-lg shadow-md transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg">
                    <i class="fas fa-paper-plane mr-2"></i>Publikasikan Keresahan
                </button>
            </form>
        </div>

        <!-- Pencarian dan Filter dengan design lebih modern -->
        <div id="daftar-curhat" class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6 p-6 animate-fadeIn" style="animation-delay: 0.2s;">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <div class="w-full md:w-1/2">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-book-open mr-3 text-indigo-600"></i>
                        <?= !empty($search) ? 'Hasil Pencarian' : 'Keresahan Terkini' ?>
                    </h2>
                    <p class="text-gray-500 mt-1"><?= $result->num_rows ?> hasil ditemukan</p>
                </div>
                
                <div class="w-full md:w-auto flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                    <form method="GET" class="relative w-full md:w-64">
                        <input type="text" name="search" placeholder="Cari keresahan..." 
                               value="<?= htmlspecialchars($search) ?>"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all duration-300">
                        <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    </form>
                    
                    <form method="GET" class="flex items-center space-x-2">
                        <select name="kategori" onchange="this.form.submit()" 
                                class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all duration-300">
                            <option value="semua">Semua Kategori</option>
                            <option value="akademik" <?= $kategori_filter == 'akademik' ? 'selected' : '' ?>>Akademik</option>
                            <option value="fasilitas" <?= $kategori_filter == 'fasilitas' ? 'selected' : '' ?>>Fasilitas</option>
                            <option value="organisasi" <?= $kategori_filter == 'organisasi' ? 'selected' : '' ?>>Organisasi</option>
                            <option value="dosen" <?= $kategori_filter == 'dosen' ? 'selected' : '' ?>>Dosen</option>
                            <option value="lainnya" <?= $kategori_filter == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                        <?php if (!empty($search) || $kategori_filter != 'semua'): ?>
                            <a href="?" class="px-3 py-2.5 text-indigo-600 hover:text-indigo-800 transition-colors duration-300">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Keresahan dengan card modern -->
        <?php if ($result->num_rows == 0): ?>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-10 text-center animate-fadeIn">
                <img src="https://placehold.co/300x200/png?text=📭" alt="Ilustrasi kotak surat kosong" class="mx-auto mb-6 w-48 opacity-80">
                <h3 class="text-xl font-medium text-gray-700 mb-2">Belum ada keresahan</h3>
                <p class="text-gray-500 max-w-md mx-auto">Jadilah yang pertama membagikan keluh kesah atau masalah yang kamu hadapi di kampus</p>
                <a href="#form-curhat" class="mt-6 inline-block px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-all duration-300">
                    <i class="fas fa-plus mr-2"></i>Tulis Keresahan
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-5 mb-10">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover animate-fadeIn">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <span class="tag px-3 py-1 bg-indigo-100 text-indigo-800 text-xs rounded-full font-medium">
                                            <?= htmlspecialchars($row['kategori']) ?>
                                        </span>
                                        <span class="tag px-3 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-medium">
                                            <?= htmlspecialchars($row['fakultas']) ?>
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                                        <?= htmlspecialchars($row['judul']) ?>
                                    </h3>
                                    <p class="text-gray-600 mb-4">
                                        <?= nl2br(htmlspecialchars($row['isi'])) ?>
                                    </p>
                                </div>
                                <span class="text-sm text-gray-400 whitespace-nowrap ml-4">
                                    <?= date('d M Y', strtotime($row['waktu'])) ?>
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <div class="flex items-center">
                                    <img src="https://placehold.co/32x32/png?text=👤" 
                                         alt="Profil anonim" 
                                         class="w-8 h-8 rounded-full mr-2">
                                    <span class="text-sm text-gray-500">
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </span>
                                </div>
                                <div class="flex space-x-4">
                                    <button class="flex items-center text-gray-400 hover:text-indigo-600 transition-colors duration-300">
                                        <i class="far fa-thumbs-up mr-1.5"></i>
                                        <span class="text-xs font-medium">12</span>
                                    </button>
                                    <button class="flex items-center text-gray-400 hover:text-indigo-600 transition-colors duration-300">
                                        <i class="far fa-comment mr-1.5"></i>
                                        <span class="text-xs font-medium">3</span>
                                    </button>
                                    <button class="flex items-center text-gray-400 hover:text-indigo-600 transition-colors duration-300">
                                        <i class="fas fa-share-alt mr-1.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="flex justify-center mb-10">
            <nav class="inline-flex rounded-md shadow-sm">
                <a href="#" class="px-4 py-2 border border-gray-300 rounded-l-lg bg-white text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="#" class="px-4 py-2 border-t border-b border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50">
                    1
                </a>
                <a href="#" class="px-4 py-2 border-t border-b border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                    2
                </a>
                <a href="#" class="px-4 py-2 border-t border-b border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                    3
                </a>
                <a href="#" class="px-4 py-2 border border-gray-300 rounded-r-lg bg-white text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </nav>
        </div>
    </div>

    <!-- Footer modern -->
    <footer class="bg-gray-900 text-white pt-12 pb-6">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-comment-dots mr-2 text-indigo-400"></i> Curhat Kampus
                    </h3>
                    <p class="text-gray-400 text-sm">Platform curahan hati mahasiswa untuk kehidupan kampus yang lebih baik.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Navigasi</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Beranda</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Tentang</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Kategori</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Akademik</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Fasilitas</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Organisasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Lainnya</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Kontak</h4>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-indigo-400"></i>
                            <span class="text-gray-400 text-sm">curhat@kampus.id</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fab fa-instagram text-indigo-400"></i>
                            <span class="text-gray-400 text-sm">@curhatkampus</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fab fa-twitter text-indigo-400"></i>
                            <span class="text-gray-400 text-sm">@curhatkampus</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm mb-4 md:mb-0">© 2023 Curhat Kampus. All rights reserved.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Auto expand textarea
        document.querySelector('textarea[name="isi"]').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Animasi saat scroll
        document.addEventListener('DOMContentLoaded', () => {
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.animate-fadeIn');
                elements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    const isVisible = (rect.top <= window.innerHeight * 0.8);
                    
                    if (isVisible) {
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Set initial state
            document.querySelectorAll('.animate-fadeIn').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            });
            
            // Run once on load
            animateOnScroll();
            
            // Run on scroll
            window.addEventListener('scroll', animateOnScroll);
        });
    </script>
</body>
</html>
