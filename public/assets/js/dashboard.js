(function () {
    var el = document.getElementById('attendanceChart');
    var dataEl = document.getElementById('attendanceTrendData');

    if (!el || !dataEl || typeof Chart === 'undefined') {
        return;
    }

    var payload = { labels: [], values: [] };

    try {
        var parsed = JSON.parse(dataEl.textContent || '{}');
        if (Array.isArray(parsed.labels)) {
            payload.labels = parsed.labels;
        }

        if (Array.isArray(parsed.values)) {
            payload.values = parsed.values;
        }
    } catch (error) {
        payload = { labels: [], values: [] };
    }

    if (payload.labels.length === 0 || payload.values.length === 0) {
        payload = {
            labels: ['No data'],
            values: [0]
        };
    }

    new Chart(el, {
        type: 'line',
        data: {
            labels: payload.labels,
            datasets: [{
                label: 'Present',
                data: payload.values,
                borderColor: '#0b3d91',
                backgroundColor: 'rgba(11, 61, 145, 0.12)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#0b3d91',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0b1f3a',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    padding: 10,
                    cornerRadius: 4,
                    borderColor: '#b68409',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    ticks: { color: '#4a5a75', font: { size: 11, weight: '600' } },
                    grid: { color: 'rgba(198, 204, 216, 0.4)', drawBorder: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#4a5a75',
                        precision: 0,
                        font: { size: 11, weight: '600' }
                    },
                    grid: { color: 'rgba(198, 204, 216, 0.4)', drawBorder: false }
                }
            }
        }
    });
})();
