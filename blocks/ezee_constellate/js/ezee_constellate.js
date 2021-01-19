function loadPercentageGraph(Y, d) {
    var dataSummary = d;

    setTimeout(function () {
        if (dataSummary != null) {
            var percentage = (parseInt(dataSummary.completedcourses) / parseInt(dataSummary.assignedcourses)) * 100;
            $('#ChartPercentage').text(percentage + '%');

            new Chart(document.getElementById('PercentageChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Completed', 'Incomplete'],
                    datasets: [{
                        data: [parseInt(dataSummary.completedcourses), (parseInt(dataSummary.assignedcourses) - parseInt(dataSummary.completedcourses))],
                        backgroundColor: ['#241168', '#eaeaea'],
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    cutoutPercentage: 50,
                    title: {
                        display: true,
                        text: 'Team Course Completion'
                    },
                    legend: {
                        display: false,
                        position: 'bottom',
                        fullWidth: false,
                        labels: {
                            boxWidth: 20
                        }
                    },
                    datalabels: {
                        display: false
                    },
                    tooltips: {
                        enabled: true
                    }
                }
            });
        }
    }, 100);
}


function loadUserGraph(Y, d) {
    var dataUser = JSON.parse(d);
    var chartData = [];
    var chartLabels = [];

    setTimeout(function () {
        if (dataUser != null) {
            for (var i = 0; i < dataUser.length; i++) {
                chartLabels.push(dataUser[i].displayname);
                chartData.push(parseInt(dataUser[i].completedcourses));
            }

            new Chart(document.getElementById('UserChart').getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    datasets: [{
                        data: chartData,
                        backgroundColor: '#57c5d7'
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'User Completions'
                    },
                    scales: {
                        yAxes: [{
                            type: 'category',
                            labels: chartLabels,
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                autoSkip: false,
                                beginAtZero: true
                            }
                        }],
                        xAxes: [{
                            display: false,
                            ticks: {
                                autoSkip: false,
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    aspectRatio: 2,
                    animation: {
                        duration: 3000
                    },
                    cornerRadius: 5
                }
            });
        }
    }, 100);
}


function loadDateGraph(Y, d) {
    var dataDates = JSON.parse(d);
    var lineMonths = [];
    var lineData = [];

    //Get month dates for line graph
    var d = new Date(),
        n = d.getMonth();
    var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    for (var i = n; i >= 0; --i) {
        lineMonths.push(months[i]);
    }
    for (var i = 11; i >= (n + 1); --i) {
        lineMonths.push(months[i]);
    }

    for (var i = 0; i < lineMonths.length; i++) {
        $.each(dataDates, function (k, d) {
            if (months[d.months-1] == lineMonths[i]) {
                lineData.push(parseInt(d.logins));
            }
        });
    }  

    var datasets = [{
        label: 'Logins',
        data: lineData.reverse(),
        backgroundColor: '#57c5d7',
        borderColor: '#57c5d7',
        fill: false
    }]

    setTimeout(function () {
        new Chart(document.getElementById('DateChart').getContext('2d'), {
            type: 'line',
            data: {
                datasets: datasets
            },
            options: {
                title: {
                    display: true,
                    text: 'Activity (last 12 months)',
                    fontColor: '#797979'
                },
                scales: {
                    yAxes: [{
                        display: false,
                        ticks: {
                            autoSkip: false,
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        type: 'category',
                        labels: months.reverse(),
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            autoSkip: false,
                            beginAtZero: true
                        }
                    }],
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 20
                    }
                },
                aspectRatio: 2,
                animation: {
                    duration: 3000
                }
            }
        });
    }, 100);
}