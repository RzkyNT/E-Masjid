<?php
http_response_code(503);
header("Retry-After: 3600");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>503 - Sedang Maintenance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center text-center">
        <div>
            <h1 class="text-5xl font-bold text-blue-600 mb-4">503</h1>
            <p class="text-gray-600 mb-6">
                Website sedang dalam perawatan. Silakan coba lagi nanti.
            </p>
        </div>
    </div>
</body>
</html>

