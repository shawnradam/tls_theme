document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    var charts = {
        'marketTrendChart': {
            type: 'line',
            data: {
                labels: ['2020', '2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'Transaction Value (Trillion RM)',
                    data: [2.8, 3.2, 4.1, 3.57, 5.17],
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Sabah Property Transaction Value Trend (2020-2024)', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Source: Sabah Lands and Surveys Department', font: { size: 10, style: 'italic' } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Value (Trillion RM)' } } },
                animation: { duration: 1500, easing: 'easeOutQuart' }
            }
        },
        'roiComparisonChart': {
            type: 'bar',
            data: {
                labels: ['Annual ROI (%)', 'Liquidity Score', 'Market Size', 'Bank Accessibility', 'Growth Potential'],
                datasets: [
                    {
                        label: 'Native Title (NT)',
                        data: [9, 3, 2, 2, 6],
                        backgroundColor: 'rgba(22, 163, 74, 0.7)',
                        borderColor: '#16a34a',
                        borderWidth: 2
                    },
                    {
                        label: 'Country Lease (CL)',
                        data: [16.5, 8, 9, 8, 10],
                        backgroundColor: 'rgba(3, 105, 161, 0.7)',
                        borderColor: '#0369a1',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'NT vs CL: Investment Comparison (Scale 1-10)', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Higher = Better for Investment', font: { size: 10, style: 'italic' } }
                },
                scales: { y: { beginAtZero: true, max: 12 } },
                animation: { duration: 1200, easing: 'easeOutQuart' }
            }
        },
        'investmentChart': {
            type: 'doughnut',
            data: {
                labels: ['Manufacturing (SK Nexilis)', 'Solar/Semiconductor', 'SME Industrial', 'Logistics & Warehousing', 'Other'],
                datasets: [{
                    data: [35, 25, 22, 12, 6],
                    backgroundColor: ['#16a34a', '#0d9488', '#0891b2', '#6366f1', '#8b5cf6'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'KKIP Investment by Sector (%)', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Total: RM8.32 Billion (2017-2024)', font: { size: 11, style: 'italic' } },
                    legend: { position: 'right', labels: { padding: 12, font: { size: 11 } } }
                },
                animation: { duration: 1500, easing: 'easeOutQuart' }
            }
        },
        'pantasGrowthChart': {
            type: 'bar',
            data: {
                labels: ['2020', '2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'Registered Plans',
                    data: [1250, 1580, 1890, 2150, 2450],
                    backgroundColor: 'rgba(22, 163, 74, 0.7)',
                    borderColor: '#16a34a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'PANTAS Registration Growth (2020-2024)', font: { size: 14, weight: 'bold' } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Plans' } } },
                animation: { duration: 1200 }
            }
        },
        'residential2022Chart': {
            type: 'bar',
            data: {
                labels: ['Kota Kinabalu', 'Penampang', 'Putatan', 'Tuaran', 'Kinarut'],
                datasets: [{
                    label: 'Transactions',
                    data: [2850, 1450, 890, 620, 480],
                    backgroundColor: 'rgba(3, 105, 161, 0.7)',
                    borderColor: '#0369a1',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Residential Transactions by District (2022)', font: { size: 14, weight: 'bold' } }
                },
                animation: { duration: 1000 }
            }
        },
        'residential2023Chart': {
            type: 'line',
            data: {
                labels: ['Q1 2023', 'Q2 2023', 'Q3 2023', 'Q4 2023'],
                datasets: [{
                    label: 'KK Conurbation',
                    data: [680, 720, 850, 830],
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Other Districts',
                    data: [520, 480, 550, 580],
                    borderColor: '#0369a1',
                    backgroundColor: 'rgba(3, 105, 161, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Residential Transactions by Quarter (2023)', font: { size: 14, weight: 'bold' } }
                },
                animation: { duration: 1200 }
            }
        },
        'tcrChart': {
            type: 'doughnut',
            data: {
                labels: ['Turun Haji (45%)', 'Dividen Saham (28%)', 'Simpanan Pelaburan (18%)', 'Lain-lain (9%)'],
                datasets: [{
                    data: [45, 28, 18, 9],
                    backgroundColor: ['#16a34a', '#0d9488', '#0891b2', '#6366f1'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Tunai CRISPR Distribution 2024', font: { size: 14, weight: 'bold' } }
                },
                animation: { duration: 1200 }
            }
        },
        'panBorneoChart': {
            type: 'line',
            data: {
                labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
                datasets: [{
                    label: 'Sabah Length (km)',
                    data: [0, 0, 425, 475, 492, 505],
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Sarawak Length (km)',
                    data: [0, 0, 320, 355, 362, 368],
                    borderColor: '#0369a1',
                    backgroundColor: 'rgba(3, 105, 161, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Pan Borneo Highway: Sabah vs Sarawak Progress', font: { size: 14, weight: 'bold' } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Length Completed (km)' } } },
                animation: { duration: 1500 }
            }
        },
        'ncrDistributionChart': {
            type: 'doughnut',
            data: {
                labels: ['Continuous Occupation', 'Fruit Trees', 'Economic Plants', 'Grazing', 'Built/Cultivated', 'Burial Grounds', 'Rights of Way'],
                datasets: [{
                    data: [65, 18, 8, 5, 12, 2, 4],
                    backgroundColor: ['#16a34a', '#0d9488', '#0891b2', '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { padding: 15, font: { size: 11 } } },
                    title: { display: true, text: 'NCR Claim Types Distribution (%)', font: { size: 14, weight: 'bold' } }
                },
                animation: { duration: 1200, easing: 'easeOutQuart' }
            }
        },
        'ntclPieChart': {
            type: 'pie',
            data: {
                labels: ['Native Title (NT) - 54%', 'Country Lease (CL) - 46%'],
                datasets: [{
                    data: [54, 46],
                    backgroundColor: ['#16a34a', '#0369a1'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, font: { size: 13 } } },
                    title: { display: true, text: 'Land Title Distribution: NT vs CL', font: { size: 15, weight: 'bold' } },
                    subtitle: { display: true, text: 'Based on JTU Statistics (March 2024)', font: { size: 11, style: 'italic' } }
                },
                animation: { animateRotate: true, duration: 1500 }
            }
        },
        'titlesTimeline': {
            type: 'bar',
            data: {
                labels: ['2020', '2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'NT Titles Granted',
                    data: [2500, 3200, 4100, 4370, 2590],
                    backgroundColor: '#16a34a',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Annual NT Title Issuances (2020-2024)', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Source: Jabatan Tanah dan Ukur (JTU) Statistics', font: { size: 10, style: 'italic' } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Titles' } } },
                animation: { duration: 1200, easing: 'easeOutQuart' }
            }
        },
        'panBorneoChart': {
            type: 'bar',
            data: {
                labels: ['WP01', 'WP02', 'WP05', 'WP06', 'WP15', 'WP21', 'WP27'],
                datasets: [{
                    label: 'Completion %',
                    data: [64.99, 50.58, 100, 87.95, 100, 100, 100],
                    backgroundColor: ['#f59e0b', '#f59e0b', '#16a34a', '#f59e0b', '#16a34a', '#16a34a', '#16a34a'],
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Pan Borneo Highway - Phase 1A Progress', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Orange = In Progress, Green = Completed', font: { size: 10, style: 'italic' } },
                    legend: { display: false }
                },
                scales: { x: { max: 100, title: { display: true, text: 'Completion Percentage' } } },
                animation: { duration: 1000 }
            }
        },
        'quarterlyChart2022': {
            type: 'bar',
            data: {
                labels: ['Q1 2022', 'Q2 2022', 'Q3 2022', 'Q4 2022'],
                datasets: [{
                    label: 'Transactions',
                    data: [1342, 1431, 1700, 1319],
                    backgroundColor: ['#16a34a', '#0d9488', '#0891b2', '#6366f1'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Quarterly Residential Transactions 2022', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Source: Rahim & Co Kota Kinabalu Housing Property Monitor 4Q2022', font: { size: 10, style: 'italic' } }
                },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Transactions' } } },
                animation: { duration: 1000, easing: 'easeOutBounce' }
            }
        },
        'annualCompChart': {
            type: 'bar',
            data: {
                labels: ['2021', '2022', '2023'],
                datasets: [
                    {
                        label: 'Transaction Volume',
                        data: [4806, 5792, 5689],
                        backgroundColor: '#16a34a',
                        borderRadius: 6
                    },
                    {
                        label: 'Transaction Value (in thousands)',
                        data: [2370, 2780, 2370],
                        type: 'line',
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Sabah Residential Transactions 2021-2023', font: { size: 14, weight: 'bold' } },
                    subtitle: { display: true, text: 'Source: Rahim & Co Kota Kinabalu Housing Property Monitor 4Q2023', font: { size: 10, style: 'italic' } }
                },
                scales: {
                    y: { type: 'linear', position: 'left', title: { display: true, text: 'Volume' } },
                    y1: { type: 'linear', position: 'right', title: { display: true, text: 'Value (RM million)' }, grid: { drawOnChartArea: false } }
                },
                animation: { duration: 1200 }
            }
        },
        'pantasGrowthChart': {
            type: 'bar',
            data: {
                labels: ['2012', '2014', '2016', '2018', '2020', '2022'],
                datasets: [
                    {
                        label: 'Annual NT Grants',
                        data: [1200, 2100, 3500, 4200, 5100, 8472],
                        backgroundColor: '#16a34a',
                        borderRadius: 6
                    },
                    {
                        label: 'Native Land Ownership (%)',
                        data: [24.5, 28, 32, 36, 40, 42.7],
                        type: 'line',
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'PANTAS Programme Growth & Native Land Ownership', font: { size: 14, weight: 'bold' } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { type: 'linear', position: 'left', title: { display: true, text: 'Number of Grants' } },
                    y1: { type: 'linear', position: 'right', title: { display: true, text: 'Ownership %' }, grid: { drawOnChartArea: false }, min: 20, max: 50 }
                },
                animation: { duration: 1200, easing: 'easeOutQuart' }
            }
        }
    };

    Object.keys(charts).forEach(function(chartId) {
        var ctx = document.getElementById(chartId);
        if (ctx) {
            new Chart(ctx, charts[chartId]);
        }
    });
});