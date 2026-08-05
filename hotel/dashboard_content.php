<!-- hotel/dashboard_content.php -->
<div id="dashboard-wrapper" class="opacity-0 max-w-7xl mx-auto pb-12">

    <header class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-1">Welcome Back!</h1>
            <p class="text-sm text-slate-500 font-medium">Here's what's happening with your property today.</p>
        </div>
        <div class="hidden md:block">
            <span class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-lg shadow-sm text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-calendar-day text-blue-500"></i> <?= date('F j, Y') ?>
            </span>
        </div>
    </header>

    <!-- KPI Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-calendar-check text-6xl text-blue-600"></i>
            </div>
            <div class="relative z-10">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                    <i class="fas fa-calendar-check text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Total Bookings</h3>
                <p class="text-3xl font-black text-slate-800">1,234</p>
                <p class="text-xs text-emerald-500 font-bold mt-2"><i class="fas fa-arrow-up mr-1"></i> 12% vs last month</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wallet text-6xl text-emerald-600"></i>
            </div>
            <div class="relative z-10">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <i class="fas fa-rupee-sign text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Revenue (This Month)</h3>
                <p class="text-3xl font-black text-slate-800">₹45,678</p>
                <p class="text-xs text-emerald-500 font-bold mt-2"><i class="fas fa-arrow-up mr-1"></i> 8.4% vs last month</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-chart-pie text-6xl text-indigo-600"></i>
            </div>
            <div class="relative z-10">
                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                    <i class="fas fa-percentage text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Occupancy Rate</h3>
                <p class="text-3xl font-black text-slate-800">78%</p>
                <p class="text-xs text-emerald-500 font-bold mt-2"><i class="fas fa-arrow-up mr-1"></i> 2% vs last month</p>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-door-open text-6xl text-amber-600"></i>
            </div>
            <div class="relative z-10">
                <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                    <i class="fas fa-bed text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Active Rooms</h3>
                <p class="text-3xl font-black text-slate-800">12</p>
                <p class="text-xs text-slate-400 font-medium mt-2">Ready for guests</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-chart-line text-blue-500"></i> Monthly Revenue</h3>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Utilization Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-chart-bar text-indigo-500"></i> Room Utilization</h3>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="utilizationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // GSAP Entrance
        if (typeof gsap !== 'undefined') {
            gsap.to("#dashboard-wrapper", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out"
            });
        } else {
            document.getElementById('dashboard-wrapper').style.opacity = 1;
        }

        // Global Chart Defaults for "Pro" Look
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.scale.grid.color = '#f1f5f9';

        // 1. Revenue Chart (Curved Line)
        const ctxRev = document.getElementById('revenueChart').getContext('2d');

        // Create Gradient
        const gradientBlue = ctxRev.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
        gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: [12000, 19000, 15000, 26000, 28000, 45678],
                    borderColor: '#3b82f6',
                    backgroundColor: gradientBlue,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        border: {
                            dash: [4, 4]
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });

        // 2. Utilization Chart (Rounded Bars)
        const ctxUtil = document.getElementById('utilizationChart').getContext('2d');
        new Chart(ctxUtil, {
            type: 'bar',
            data: {
                labels: ['Deluxe 101', 'Suite 201', 'Standard 102', 'Standard 103', 'Villa 501'],
                datasets: [{
                    label: 'Bookings This Month',
                    data: [12, 19, 8, 5, 2],
                    backgroundColor: [
                        '#3b82f6', // Blue
                        '#8b5cf6', // Indigo
                        '#10b981', // Emerald
                        '#f59e0b', // Amber
                        '#ef4444' // Rose
                    ],
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [4, 4]
                        }
                    }
                }
            }
        });
    });
</script>