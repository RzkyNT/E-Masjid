<!DOCTYPE html>
<html>
<head>
    <title>Test Prayer API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Test MyQuran Prayer API</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Today's Prayer Times -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Today's Prayer Times</h2>
                <button onclick="testTodayAPI()" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Test Today API</button>
                <div id="today-result" class="bg-gray-50 p-4 rounded text-sm"></div>
            </div>
            
            <!-- Monthly Prayer Times -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Monthly Prayer Times</h2>
                <button onclick="testMonthlyAPI()" class="bg-green-500 text-white px-4 py-2 rounded mb-4">Test Monthly API</button>
                <div id="monthly-result" class="bg-gray-50 p-4 rounded text-sm"></div>
            </div>
            
            <!-- Direct MyQuran API Test -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Direct MyQuran API</h2>
                <button onclick="testDirectAPI()" class="bg-purple-500 text-white px-4 py-2 rounded mb-4">Test Direct API</button>
                <div id="direct-result" class="bg-gray-50 p-4 rounded text-sm"></div>
            </div>
            
            <!-- Cache Test -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Cache Test</h2>
                <button onclick="testCache()" class="bg-yellow-500 text-white px-4 py-2 rounded mb-4">Test Cache</button>
                <div id="cache-result" class="bg-gray-50 p-4 rounded text-sm"></div>
            </div>
        </div>
    </div>

    <script>
        async function testTodayAPI() {
            const resultDiv = document.getElementById('today-result');
            resultDiv.innerHTML = 'Loading...';
            
            try {
                const response = await fetch('./api/prayer_times.php?action=today');
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<span class="text-red-500">Error: ' + error.message + '</span>';
            }
        }
        
        async function testMonthlyAPI() {
            const resultDiv = document.getElementById('monthly-result');
            resultDiv.innerHTML = 'Loading...';
            
            try {
                const response = await fetch('./api/prayer_times.php?action=monthly&year=2025&month=12');
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<span class="text-red-500">Error: ' + error.message + '</span>';
            }
        }
        
        async function testDirectAPI() {
            const resultDiv = document.getElementById('direct-result');
            resultDiv.innerHTML = 'Loading...';
            
            try {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                
                const response = await fetch(`https://api.myquran.com/v2/sholat/jadwal/1203/${year}/${month}/${day}`);
                const data = await response.json();
                resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } catch (error) {
                resultDiv.innerHTML = '<span class="text-red-500">Error: ' + error.message + '</span>';
            }
        }
        
        async function testCache() {
            const resultDiv = document.getElementById('cache-result');
            resultDiv.innerHTML = 'Checking cache...';
            
            try {
                // Check if cache directory exists
                const response = await fetch('./api/cache/');
                const cacheStatus = response.ok ? 'Cache directory accessible' : 'Cache directory not accessible';
                
                // List cache files
                const cacheFiles = [];
                // This would need server-side implementation to list files
                
                resultDiv.innerHTML = `
                    <p><strong>Cache Status:</strong> ${cacheStatus}</p>
                    <p><strong>Cache Directory:</strong> ./api/cache/</p>
                    <p><strong>Note:</strong> Cache files are created automatically when API is called</p>
                `;
            } catch (error) {
                resultDiv.innerHTML = '<span class="text-red-500">Error: ' + error.message + '</span>';
            }
        }
    </script>
</body>
</html>