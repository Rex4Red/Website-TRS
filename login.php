<?php
session_start();

// Deklarasi jurusan berdasarkan 3 digit NIM
$jurusan = [
    '123' => 'Jurusan Informatika',
    '124' => 'Jurusan Sistem Informasi',
    '345' => 'Fakultas Ekonomi',
    '456' => 'Fakultas Hukum',
    '567' => 'Fakultas Pertanian',
    // Tambahkan jurusan lainnya sesuai kebutuhan
];

$message = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $nim_input = $_POST['nim'] ?? '';
    $nim_prefix = substr($nim_input, 0, 3); // Ambil 3 digit pertama

    if (array_key_exists($nim_prefix, $jurusan)) {
        $_SESSION['nim'] = $nim_input;
        $_SESSION['jurusan'] = $jurusan[$nim_prefix];
        header("Location: index.php"); // Redirect ke halaman utama
        exit();
    } else {
        $message = "NIM tidak valid. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Mahasiswa - Curhat Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-8 w-96">
        <h2 class="text-2xl font-bold mb-4 text-center">Login Mahasiswa</h2>
        <?php if ($message): ?>
            <div class="mb-4 text-red-500 text-center"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label for="nim" class="block text-sm font-medium mb-1">Masukkan NIM anda</label>
                <input type="text" name="nim" id="nim" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>
            <button type="submit" name="login" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
    </div>
</body>
</html>
