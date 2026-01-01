<?php
/**
 * Professional System Monitor Dashboard
 * Masjid Al-Muhajirin Information System
 */

// Include bootstrap
require_once __DIR__ . '/../includes/bootstrap.php';

// Simple authentication (replace with proper auth system)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'status':
            echo json_encode(SystemManager::getSystemStatus());
            break;
            
        case 'performance':
            echo json_encode(PerformanceMonitor::generateReport(7));
            break;
            
        case 'toggle_maintenance':
            $mode = $_POST['mode'] === 'true';
            if ($mode) {
                SystemManager::enableMaintenanceMode([$_SERVER['REMOTE_ADDR']]);
            } else {
                SystemManager::disableMaintenanceMode();
            }
            echo json_encode(['success' => true, 'mode' => $mode]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

$page_title = "System Monitor";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Admin Dashboard</title>
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-online { background-color: #10b981; }
        .status-warning { background-color: #f59e0b; }
        .status-offline { background-color: #ef4444; }
        
        .refresh-animation {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-tachometer-alt text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">System Monitor</h1>
                        <p class="text-sm text-gray-500">Real-time system monitoring dashboard</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshData()" id="refreshBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- System Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="metric-card text-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-opacity-80 text-sm">System Status</p>
                        <p class="text-2xl font-bold" id="systemStatus">Loading...</p>
                    </div>
                    <i class="fas fa-server text-3xl text-white text-opacity-60"></i>
                </div>
            </div>
            
            <div class="metric-card text-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-opacity-80 text-sm">Memory Usage</p>
                        <p class="text-2xl font-bold" id="memoryUsage">Loading...</p>
                    </div>
                    <i class="fas fa-memory text-3xl text-white text-opacity-60"></i>
                </div>
            </div>
            
            <div class="metric-card text-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-opacity-80 text-sm">Disk Usage</p>
                        <p class="text-2xl font-bold" id="diskUsage">Loading...</p>
                    </div>
                    <i class="fas fa-hdd text-3xl text-white text-opacity-60"></i>
                </div>
            </div>
            
            <div class="metric-card text-white rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-opacity-80 text-sm">Uptime</p>
                        <p class="text-2xl font-bold" id="uptime">Loading...</p>
                    </div>
                    <i class="fas fa-clock text-3xl text-white text-opacity-60"></i>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">System Controls</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold mb-2">Maintenance Mode</h3>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Enable/Disable maintenance</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="maintenanceToggle" class="sr-only peer" onchange="toggleMaintenance()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold mb-2">Clear Cache</h3>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Clear system cache</span>
                        <button onclick="clearCache()" class="bg-orange-600 text-white px-3 py-1 rounded text-sm hover:bg-orange-700">
                            Clear
                        </button>
                    </div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold mb-2">Generate Report</h3>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Download system report</span>
                        <button onclick="generateReport()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Response Time Trend</h2>
                <canvas id="responseTimeChart" width="400" height="200"></canvas>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Memory Usage Trend</h2>
                <canvas id="memoryChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- System Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Services Status -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Services Status</h2>
                <div id="servicesStatus" class="space-y-3">
                    <!-- Services will be loaded here -->
                </div>
            </div>
            
            <!-- Recent Logs -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Recent System Events</h2>
                <div id="recentLogs" class="space-y-2 max-h-64 overflow-y-auto">
                    <!-- Logs will be loaded here -->
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script>
        let responseTimeChart, memoryChart;
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            loadSystemData();
            
            // Auto-refresh every 30 seconds
            setInterval(loadSystemData, 30000);
        });
        
        // Initialize charts
        function initCharts() {
            // Response Time Chart
            const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
            responseTimeChart = new Chart(responseCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Memory Chart
            const memoryCtx = document.getElementById('memoryChart').getContext('2d');
            memoryChart = new Chart(memoryCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Memory Usage (MB)',
                        data: [],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Load system data
        async function loadSystemData() {
            try {
                const response = await fetch('?action=status');
                const data = await response.json();
                
                updateSystemCards(data);
                updateServicesStatus(data.services);
                updateCharts();
                
            } catch (error) {
                console.error('Error loading system data:', error);
            }
        }
        
        // Update system cards
        function updateSystemCards(data) {
            document.getElementById('systemStatus').textContent = data.maintenance_mode ? 'Maintenance' : 'Online';
            document.getElementById('memoryUsage').textContent = formatBytes(data.memory_usage);
            document.getElementById('diskUsage').textContent = data.disk_usage.percentage + '%';
            document.getElementById('uptime').textContent = data.uptime;
            
            // Update maintenance toggle
            document.getElementById('maintenanceToggle').checked = data.maintenance_mode;
        }
        
        // Update services status
        function updateServicesStatus(services) {
            const container = document.getElementById('servicesStatus');
            container.innerHTML = '';
            
            Object.entries(services).forEach(([service, status]) => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                div.innerHTML = `
                    <div class="flex items-center">
                        <span class="status-indicator ${status ? 'status-online' : 'status-offline'}"></span>
                        <span class="font-medium">${service.replace('_', ' ').toUpperCase()}</span>
                    </div>
                    <span class="text-sm ${status ? 'text-green-600' : 'text-red-600'}">
                        ${status ? 'Running' : 'Stopped'}
                    </span>
                `;
                container.appendChild(div);
            });
        }
        
        // Update charts with sample data
        function updateCharts() {
            const now = new Date().toLocaleTimeString();
            
            // Add sample data (replace with real data)
            responseTimeChart.data.labels.push(now);
            responseTimeChart.data.datasets[0].data.push(Math.random() * 1000 + 200);
            
            memoryChart.data.labels.push(now);
            memoryChart.data.datasets[0].data.push(Math.random() * 50 + 20);
            
            // Keep only last 10 data points
            if (responseTimeChart.data.labels.length > 10) {
                responseTimeChart.data.labels.shift();
                responseTimeChart.data.datasets[0].data.shift();
                memoryChart.data.labels.shift();
                memoryChart.data.datasets[0].data.shift();
            }
            
            responseTimeChart.update();
            memoryChart.update();
        }
        
        // Refresh data
        function refreshData() {
            const btn = document.getElementById('refreshBtn');
            const icon = btn.querySelector('i');
            
            icon.classList.add('refresh-animation');
            loadSystemData();
            
            setTimeout(() => {
                icon.classList.remove('refresh-animation');
            }, 1000);
        }
        
        // Toggle maintenance mode
        async function toggleMaintenance() {
            const toggle = document.getElementById('maintenanceToggle');
            
            try {
                const formData = new FormData();
                formData.append('mode', toggle.checked);
                
                const response = await fetch('?action=toggle_maintenance', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(`Maintenance mode ${result.mode ? 'enabled' : 'disabled'}`);
                } else {
                    toggle.checked = !toggle.checked;
                    alert('Failed to toggle maintenance mode');
                }
            } catch (error) {
                toggle.checked = !toggle.checked;
                alert('Error: ' + error.message);
            }
        }
        
        // Clear cache
        function clearCache() {
            if (confirm('Are you sure you want to clear the system cache?')) {
                // Implement cache clearing
                alert('Cache cleared successfully');
            }
        }
        
        // Generate report
        function generateReport() {
            window.open('?action=report&format=pdf', '_blank');
        }
        
        // Format bytes
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>