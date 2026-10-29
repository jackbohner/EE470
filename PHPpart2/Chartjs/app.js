$(document).ready(function () {
    const nodeName = new URLSearchParams(window.location.search)
        .get('node_name') || 'node_1';

    $.ajax({
        url: 'js/data.php',
        method: 'GET',
        data: { node_name: nodeName },
        dataType: 'json',
        success: function (data) {
            const xAxis = [];
            const yAxis = [];

            data.forEach(function (row) {
                xAxis.push(row.time_received);
                yAxis.push(Number(row.temperature));
            });

            new Chart(document.getElementById('mycanvas'), {
                type: 'bar',
                data: {
                    labels: xAxis,
                    datasets: [{
                        label: 'Temperature - ' + nodeName,
                        backgroundColor: 'rgba(0, 128, 0, 0.65)',
                        borderColor: 'green',
                        borderWidth: 1,
                        data: yAxis
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Sensor ' + nodeName
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Time' }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Temperature (°C)' }
                        }
                    }
                }
            });
        },
        error: function (xhr) {
            console.error('Could not load chart data:', xhr.responseText);
        }
    });
});
