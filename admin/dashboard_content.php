<div id="dashboard-wrapper" class="opacity-0"> <!-- Opacity 0 for GSAP reveal -->

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Welcome back, <?= escape($_SESSION['admin_username'] ?? 'Admin') ?></h1>
            <p class="text-slate-500 mt-1">Here is what's happening at <span class="font-semibold text-blue-600">Traveltara</span> today.</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-lg shadow-sm hover:bg-slate-50 transition-colors flex items-center gap-2 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- Stats Cards (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        <!-- Total Destinations -->
        <div class="kpi-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Destinations</p>
                <h3 class="text-3xl font-bold text-slate-800">24</h3>
                <p class="text-xs text-emerald-500 font-medium mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    +2 this month
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Total Packages -->
        <div class="kpi-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Active Packages</p>
                <h3 class="text-3xl font-bold text-slate-800">56</h3>
                <p class="text-xs text-emerald-500 font-medium mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    +12% vs last month
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>

        <!-- Hotels Connected -->
        <div class="kpi-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow">
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Partner Hotels</p>
                <h3 class="text-3xl font-bold text-slate-800">18</h3>
                <p class="text-xs text-amber-500 font-medium mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                    Steady growth
                </p>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m3-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="kpi-card bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-2xl shadow-lg flex items-center justify-between group hover:shadow-xl transition-shadow relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>

            <div class="relative z-10">
                <p class="text-sm font-medium text-slate-300 mb-1">Revenue (This Month)</p>
                <h3 class="text-3xl font-bold text-white">₹1,24,500</h3>
                <p class="text-xs text-emerald-400 font-medium mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    +8.4% vs last month
                </p>
            </div>
            <div class="relative z-10 w-12 h-12 bg-white/10 text-emerald-400 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Charts Row 1: Primary Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- Revenue Trend -->
        <div class="chart-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Revenue Trend</h3>
                    <p class="text-sm text-slate-500">Earnings over the last 6 months</p>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Monthly Bookings -->
        <div class="chart-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Package Bookings</h3>
                    <p class="text-sm text-slate-500">Volume of bookings confirmed</p>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="bookingsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Secondary Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Invoices & Payments -->
        <div class="chart-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-1">Invoice Status</h3>
            <p class="text-sm text-slate-500 mb-6">Current payment collection status</p>
            <div class="relative h-56 w-full flex justify-center">
                <canvas id="invoiceChart"></canvas>
            </div>
        </div>

        <!-- Connected Hotels Trend -->
        <div class="chart-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-1">Hotel Partners</h3>
            <p class="text-sm text-slate-500 mb-6">Onboarding growth over time</p>
            <div class="relative h-56 w-full">
                <canvas id="hotelsChart"></canvas>
            </div>
        </div>

        <!-- Top Destinations -->
        <div class="chart-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg mb-1">Top Destinations</h3>
            <p class="text-sm text-slate-500 mb-6">Most popular regions by volume</p>
            <div class="relative h-56 w-full flex justify-center">
                <canvas id="destinationsChart"></canvas>
            </div>
        </div>

    </div>
</div>

<!-- Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ----------------------------------------------------
        // 1. GSAP Animations
        // ----------------------------------------------------
        gsap.to("#dashboard-wrapper", {
            opacity: 1,
            duration: 0.5
        });

        gsap.from(".kpi-card", {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: "back.out(1.2)"
        });

        gsap.from(".chart-card", {
            y: 40,
            opacity: 0,
            duration: 0.8,
            stagger: 0.15,
            ease: "power3.out",
            delay: 0.3
        });

        // ----------------------------------------------------
        // 2. Global Chart Settings
        // ----------------------------------------------------
        Chart.defaults.font.family = "'Inter', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#64748b'; // Tailwind slate-500
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)'; // slate-900
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        // ----------------------------------------------------
        // 3. Revenue Trend Chart (Line)
        // ----------------------------------------------------
        const revCtx = document.getElementById('revenueTrendChart').getContext('2d');

        // Create Gradient for line chart
        let revGradient = revCtx.createLinearGradient(0, 0, 0, 300);
        revGradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)'); // Emerald-500
        revGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: [12000, 19000, 15000, 22000, 28000, 35000], //[cite: 12]
                    borderColor: '#10b981', // emerald-500
                    backgroundColor: revGradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
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
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [4, 4],
                            color: '#f1f5f9'
                        }, // slate-100
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });

        // ----------------------------------------------------
        // 4. Bookings Chart (Bar)
        // ----------------------------------------------------
        const bookCtx = document.getElementById('bookingsChart').getContext('2d');
        new Chart(bookCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Bookings',
                    data: [65, 59, 80, 81, 56, 55], //[cite: 12]
                    backgroundColor: '#3b82f6', // blue-500
                    borderRadius: 6,
                    barThickness: 24,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [4, 4],
                            color: '#f1f5f9'
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });

        // ----------------------------------------------------
        // 5. Invoices Chart (Doughnut)
        // ----------------------------------------------------
        const invCtx = document.getElementById('invoiceChart').getContext('2d');
        new Chart(invCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Overdue'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'], // emerald, amber, red
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });

        // ----------------------------------------------------
        // 6. Connected Hotels Chart (Area Line)
        // ----------------------------------------------------
        const hotelCtx = document.getElementById('hotelsChart').getContext('2d');
        let hotelGradient = hotelCtx.createLinearGradient(0, 0, 0, 200);
        hotelGradient.addColorStop(0, 'rgba(139, 92, 246, 0.4)'); // violet-500
        hotelGradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

        new Chart(hotelCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Hotels',
                    data: [5, 8, 10, 14, 15, 18],
                    borderColor: '#8b5cf6', // violet-500
                    backgroundColor: hotelGradient,
                    borderWidth: 2,
                    pointRadius: 0, // hide points unless hovered
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        display: false,
                        beginAtZero: true
                    }, // hide y axis for sparkline feel
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });

        // ----------------------------------------------------
        // 7. Top Destinations (Polar Area)
        // ----------------------------------------------------
        const destCtx = document.getElementById('destinationsChart').getContext('2d');
        new Chart(destCtx, {
            type: 'polarArea',
            data: {
                labels: ['Goa', 'Kerala', 'Manali', 'Rajasthan'],
                datasets: [{
                    data: [80, 60, 45, 30],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)', // blue
                        'rgba(16, 185, 129, 0.7)', // emerald
                        'rgba(245, 158, 11, 0.7)', // amber
                        'rgba(139, 92, 246, 0.7)' // violet
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        ticks: {
                            display: false
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    });
</script>