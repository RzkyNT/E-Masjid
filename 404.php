<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 - Halaman Tidak Ditemukan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center text-center">
        <div>
            <h1 class="text-5xl font-bold text-gray-800 mb-4">404</h1>
            <p class="text-gray-600 mb-6">
                Halaman yang Anda cari tidak ditemukan.
            </p>
            <a href="/" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>

