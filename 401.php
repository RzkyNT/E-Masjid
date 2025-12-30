<?php
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>401 - Tidak Terautentikasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center text-center">
        <div>
            <h1 class="text-5xl font-bold text-yellow-500 mb-4">401</h1>
            <p class="text-gray-600 mb-6">
                Silakan login untuk mengakses halaman ini.
            </p>
            <a href="/login.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                Login
            </a>
        </div>
    </div>
</body>
</html>

