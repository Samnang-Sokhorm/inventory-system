<?php
/**
 * Dashboard with Charts and Alerts
 */

require_once 'config/database.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .container { max-width: 1400px; margin: 0 auto; }
        
        .header { 
            background: #2c3e50; 
            color: white; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 28px; }
        .nav-links a { 
            color: white; 
            text-decoration: none; 
            margin-left: 20px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            transition: all 0.3s;
        }
        .nav-links a:hover { background: rgba(255,255,255,0.2); }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .stat-card.blue::before { background: #3498db; }
        .stat-card.green::before { background: #27ae60; }
        .stat-card.red::before { background: #e74c3c; }
        .stat-card.orange::before { background: #f39c12; }
        
        .stat-card h3 { 
            font-size: 14px; 
            color: #777; 
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .stat-value { 
            font-size: 36px; 
            font-weight: bold; 
            color: #2c3e50; 
        }
        
        .alert-section { 
            background: white; 
            padding: 25px; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .alert-section h2 { 
            color: #2c3e50; 
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-badge { 
            background: #e74c3c; 
            color: white; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 14px;
            font-weight: 600;
        }
        
        .alert-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .alert-table th { 
            text-align: left; 
            padding: 12px; 
            background: #f8f9fa; 
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #dee2e6;
        }
        .alert-table td { 
            padding: 12px; 
            border-bottom: 1px solid #dee2e6; 
        }
        .alert-table tr:hover { 
            background: #f8f9fa; 
        }
        .alert-table tr.critical { 
            background: #fee; 
        }
        
        .stock-badge { 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px;
            font-weight: 600;
        }
        .stock-badge.critical { 
            background: #e74c3c; 
            color: white; 
        }
        .stock-badge.warning { 
            background: #f39c12; 
            color: white; 
        }
        
        .charts-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .chart-card { 
            background: white; 
            padding: 25px; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 { 
            color: #2c3e50; 
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .chart-container { 
            position: relative; 
            height: 350px; 
        }
        
        .no-data { 
            text-align: center; 
            padding: 40px; 
            color: #999; 
        }
        
        .loading { 
            text-align: center; 
            padding: 40px; 
        }
        
        .spinner { 
            display: inline-block; 
            width: 40px; 
            height: 40px; 
            border: 4px solid #f3f3f3; 
            border-top: 4px solid #2c3e50; 
            border-radius: 50%; 
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }

        @media (max-width: 768px) {
            .charts-grid { 
                grid-template-columns: 1fr; 
            }
            .stats-grid { 
                grid-template-columns: 1fr; 
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Inventory Dashboard</h1>
            <div class="nav-links">
                <a href="scan.php">🔍 Scanner</a>
                <a href="products.php">📦 Products</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card blue">
                <h3>Total Products</h3>
                <div class="stat-value" id="totalProducts">-</div>
            </div>
            <div class="stat-card green">
                <h3>Total Stock Value</h3>
                <div class="stat-value" id="totalValue">-</div>
            </div>
            <div class="stat-card red">
                <h3>Low Stock Items</h3>
                <div class="stat-value" id="lowStockCount">-</div>
            </div>
            <div class="stat-card orange">
                <h3>Stock Movements (30d)</h3>
                <div class="stat-value" id="totalMovements">-</div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="alert-section" id="alertSection" style="display: none;">
            <h2>
                ⚠️ Low Stock Alerts
                <span class="alert-badge" id="alertCount">0</span>
            </h2>
            <table class="alert-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="alertTableBody"></tbody>
            </table>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3>📊 Current Stock Levels</h3>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3>📈 Stock Movement Trends (Last 30 Days)</h3>
                <div class="chart-container">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        let barChart = null;
        let lineChart = null;

        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            // Auto-refresh every 30 seconds
            setInterval(loadDashboardData, 30000);
        });

        /**
         * Load all dashboard data
         */
        function loadDashboardData() {
            fetch('api/get_chart_data.php?type=all')
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        updateStatistics(result.data);
                        updateLowStockAlerts(result.data);
                        renderBarChart(result.data.barChart);
                        renderLineChart(result.data.lineChart);
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard data:', error);
                });
        }

        /**
         * Update statistics cards
         */
        function updateStatistics(data) {
            // Calculate from chart data
            const totalProducts = data.barChart.labels.length;
            const totalQuantity = data.barChart.quantities.reduce((a, b) => a + b, 0);
            const lowStockCount = data.lowStockCount;
            
            // Calculate total movements
            const totalIn = data.lineChart.stockIn.reduce((a, b) => a + b, 0);
            const totalOut = data.lineChart.stockOut.reduce((a, b) => a + b, 0);
            const totalMovements = totalIn + totalOut;

            document.getElementById('totalProducts').textContent = totalProducts;
            document.getElementById('totalValue').textContent = totalQuantity.toLocaleString();
            document.getElementById('lowStockCount').textContent = lowStockCount;
            document.getElementById('totalMovements').textContent = totalMovements.toLocaleString();
        }

        /**
         * Update low stock alerts table
         */
        function updateLowStockAlerts(data) {
            const alertSection = document.getElementById('alertSection');
            const alertCount = document.getElementById('alertCount');
            const tableBody = document.getElementById('alertTableBody');

            if (data.lowStockProducts && data.lowStockProducts.length > 0) {
                alertSection.style.display = 'block';
                alertCount.textContent = data.lowStockProducts.length;

                let html = '';
                data.lowStockProducts.forEach(product => {
                    const isCritical = product.quantity === 0;
                    const rowClass = isCritical ? 'critical' : '';
                    const badgeClass = isCritical ? 'critical' : 'warning';
                    const badgeText = isCritical ? 'OUT OF STOCK' : 'LOW STOCK';

                    html += `
                        <tr class="${rowClass}">
                            <td><strong>${product.name}</strong></td>
                            <td>${product.sku}</td>
                            <td><strong>${product.quantity}</strong></td>
                            <td>${product.min_stock}</td>
                            <td><span class="stock-badge ${badgeClass}">${badgeText}</span></td>
                        </tr>
                    `;
                });

                tableBody.innerHTML = html;
            } else {
                alertSection.style.display = 'none';
            }
        }

        /**
         * Render bar chart - Current stock levels
         */
        function renderBarChart(data) {
            const ctx = document.getElementById('barChart').getContext('2d');

            // Destroy existing chart if exists
            if (barChart) {
                barChart.destroy();
            }

            if (!data || data.labels.length === 0) {
                ctx.canvas.parentElement.innerHTML = '<div class="no-data">No data available</div>';
                return;
            }

            barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Current Stock',
                            data: data.quantities,
                            backgroundColor: '#3498db',
                            borderColor: '#2980b9',
                            borderWidth: 1
                        },
                        {
                            label: 'Min Stock Level',
                            data: data.minStocks,
                            backgroundColor: '#e74c3c',
                            borderColor: '#c0392b',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 10
                            }
                        }
                    }
                }
            });
        }

        /**
         * Render line chart - Stock movements over time
         */
        function renderLineChart(data) {
            const ctx = document.getElementById('lineChart').getContext('2d');

            // Destroy existing chart if exists
            if (lineChart) {
                lineChart.destroy();
            }

            if (!data || data.labels.length === 0) {
                ctx.canvas.parentElement.innerHTML = '<div class="no-data">No movement data in last 30 days</div>';
                return;
            }

            lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Stock IN',
                            data: data.stockIn,
                            borderColor: '#27ae60',
                            backgroundColor: 'rgba(39, 174, 96, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Stock OUT',
                            data: data.stockOut,
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }
    </script>
</body>
</html>