$(document).ready(function() {
    Chart.register(ChartDataLabels);
//      ----- Leads Conversion Bar Graph -----      //
    var ctx = document.getElementById('lead-dashboard').getContext('2d');
    var myChart;
    function populateYearOptions() {
    const startYear = 2020;
    const currentYear = new Date().getFullYear();
    const selectElement = document.getElementById('year-select');
    for (let year = currentYear; year >= startYear; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.text = year;
        selectElement.add(option);
        }
    selectElement.value = currentYear;
    }
    function fetchData(year) {
        $.ajax({
            url: './charts/leads_conversion_bar_chart.php?year=' + year,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data, status, xhr) {
                if (xhr.status === 200) {
                    updateChart(data);
                } else if (xhr.status === 204) {
                    updateChart([]);
                    }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", status, error, xhr);
            }
        });
    }
    function updateChart(data) {
        const allMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthlyRatios = new Array(12).fill(0);
        let maxValue = 0;
        data.forEach(function(item) {
            const monthIndex = item.month - 1;
            if (monthIndex >= 0 && monthIndex < 12) {
                const ratio = parseFloat(item.conversion_ratio);
                monthlyRatios[monthIndex] = ratio;
                maxValue = Math.max(maxValue, ratio);
            }
        });
        function updateChartOption(maxValue) {
            if(maxValue < 20) {
                return 20;
            } else {
                return maxValue + (maxValue * 0.1);
            }
        }
        myChart.data.labels = allMonths;
        myChart.data.datasets[0].data = monthlyRatios;
        const suggestedMax = updateChartOption(maxValue);
        myChart.options.scales.y.max = suggestedMax;
        myChart.update();
    }
    myChart = new Chart(ctx, {
        type: 'bar',
        data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        datasets: [{
            label: 'Lead Conversion Per Month',
            data: new Array(12).fill(0),
            backgroundColor: [ "#c1effd", "#ffe6c9", "#ffcbe8", "#ecd6fd", "#d9d2fd", "#abc4ff", "#ccdbfd", "#e5c185", "#caffbf", "#ffcfce", "#edf2fa", "#a2d2ff", "#faaac7"] // Initialize colors
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'category',
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    ticks: {
                        stepSize: 5,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: false,
                    text: 'Current Year Lead Conversion Ratio (Per Month)'
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: function(value) {
                        return value.toFixed(1) + '%';
                    },
                    font: {
                        weight: 'bold',
                        size: 10
                    }
                }
            }
        }
    });
    populateYearOptions();
    fetchData(new Date().getFullYear());
    document.getElementById('year-select').addEventListener('change', function() {
        const selectedYear = this.value;
        fetchData(selectedYear);
    });
    // ----- Total Prospect Per Status Pie Graph ----- //
    var ctxPie = document.getElementById('pie-dashboard').getContext('2d');
    var myPieChart;
    const labelColors = {
        "New Lead": "#c1effd",
        "Attempting Contact": "#fdfed3",
        "Contacted": "#ffe6c9",
        "Interested": "#ff8fab",
        "Not Interested": "#ffcbe8",
        "Unqualified": "#ecd6fd",
        "Follow-Up Required": "#d9d2fd",
        "Needs Assessment Done": "#abc4ff",
        "Proposal Sent": "#ccdbfd",
        "Negotiation In Progress": "#e5c185",
        "Closed - Won": "#caffbf",
        "Closed - Lost": "#ffcfce",
        "On Hold": "#edf2fa",
        "Client Onboarding": "#a2d2ff",
        "Repeat Business Opportunity": "#faaac7"
    };
    function fetchDataPieChart() {
        $.ajax({
            url: './charts/total_prospects_pie_chart.php',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data) {
                updatePieChart(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching pie chart data:", status, error);
            }
        });
    }
    function updatePieChart(data) {
        const labels = [];
        const dataPoints = [];
        const backgroundColors = [];
        data.forEach(item => {
            labels.push(item.status_name);
            dataPoints.push(parseInt(item.prospect_count));
            if (labelColors.hasOwnProperty(item.status_name)) {
                backgroundColors.push(labelColors[item.status_name]);
            } else {
                backgroundColors.push("#808080");
                console.warn(`No color defined for label: ${item.status_name}.  Using default gray color.`);
            }
        });
        myPieChart.data.labels = labels;
        myPieChart.data.datasets[0].data = dataPoints;
        myPieChart.data.datasets[0].backgroundColor = backgroundColors;
        myPieChart.update();
    }
    myPieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                hoverOffset: 4
        }]
    },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    align: 'center',
                    title: {
                        display: false,
                        text: 'Total Prospect Per Status'
                    },
                    labels: {
                        font:{
                            size: 10
                        }
                    }
                },
                datalabels: {
                    formatter: (value, context) => {
                        return value;
                    },
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    color: '#000000',
                    align: 'center',
                    anchor: 'center',
                }
            }
        }
    });
    fetchDataPieChart();
    // ----- Leads Won/Lost Bar Graph ----- //
    var ctx2 = document.getElementById('reportsBarGraph').getContext('2d');
    Chart.register(ChartDataLabels);
    var myChart2;
    myChart2 = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Closed - Won', 'Closed - Lost'],
            datasets: [{
                label: 'Leads',
                data: [0, 0],
                backgroundColor: ["#caffbf", "#ff8fab"]
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                }
            },
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                    text: 'Number of Leads Won/Lost'
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: Math.round,
                    font: {
                        weight: 'bold',
                        size: 14
                    }
                }
            }
        }
    });
    function updateChartData(month, year) {
        $.ajax({
            url: './charts/leads_won_lost_bar_chart.php',
            type: 'GET',
            data: { month: month, year: year },
            dataType: 'json',
            success: function(data) {
                const won = parseInt(data["closed - won"] || 0);
                const onboarding = parseInt(data["client onboarding"] || 0);
                const lost = parseInt(data["closed - lost"] || 0);
                const totalWon = won + onboarding;
                myChart2.data.datasets[0].data = [totalWon, lost];
                const maxValue = Math.max(totalWon, lost);
                const suggestedMax = updateChartOption(maxValue);
                myChart2.options.scales.y.max = suggestedMax;
                myChart2.update();
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", error, xhr.responseText);
            }
        });
    }
    $('#monthSelect2, #yearSelect2').change(function() {
        const selectedMonth = $('#monthSelect2').val();
        const selectedYear = $('#yearSelect2').val();
        updateChartData(selectedMonth, selectedYear);
    });
    updateChartData($('#monthSelect2').val(), $('#yearSelect2').val());
    // ----- Leads Growth Line Graph ----- //
    var ctxLineChart = document.getElementById('lineChartLeadsGrowth').getContext('2d');
    var myLineChart;
    myLineChart = new Chart(ctxLineChart, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            datasets: [{
                label: 'Total Leads',
                data: new Array(12).fill(0),
                borderColor: "rgb(113, 153, 248)",
                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Leads'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                    text: 'Leads Growth Per Month'
                },
                datalabels: {
                    anchor: 'center',
                    align: 'top',
                    formatter: Math.round,
                    font: {
                        weight: 'bold',
                        size: 14
                    }
                }
            }
        }
    });
    function fetchDataLineChart(year) {
        $.ajax({
            url: './charts/leads_growth_line_chart.php?year=' + year,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data) {
                updateLineChart(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching line chart data:", status, error);
            }
        });
    }
    function updateLineChart(data) {
        const monthlyCounts = new Array(12).fill(0);
        let maxValue = 0;
        data.forEach(item => {
            const monthIndex = item.month - 1;
            if (monthIndex >= 0 && monthIndex < 12) {
                const count = parseInt(item.prospect_count);
                monthlyCounts[monthIndex] = count;
                maxValue = Math.max(maxValue, count);
            }
        });
        myLineChart.data.datasets[0].data = monthlyCounts;
        const suggestedMax = updateChartOption(maxValue);
        myLineChart.options.scales.y.max = suggestedMax;
        myLineChart.update();
    }
    $('#yearSelectLineChart').change(function() {
        const selectedYear = $(this).val();
        fetchDataLineChart(selectedYear);
    });
    fetchDataLineChart($('#yearSelectLineChart').val());
    function updateChartOption(maxValue) {
        const suggestedMax = maxValue <= 10 ? 10 : maxValue + (maxValue * 0.2);
        return suggestedMax;
    }
});