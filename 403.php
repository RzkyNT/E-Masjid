<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>403 - Akses Ditolak</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center text-center">
        <div>
            <h1 class="text-5xl font-bold text-red-600 mb-4">403</h1>
            <p class="text-gray-600 mb-6">
                Anda tidak memiliki izin untuk mengakses halaman ini.
            </p>
            <a href="/" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>

