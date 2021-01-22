function loadPercentageGraph(Y, d) {
    var dataJSON = d;

    setTimeout(function () {
        if (dataJSON != null) {
            $('#ChartPercentage').text(dataJSON.completionpercentage + '%');

            new Chart(document.getElementById('PercentageChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Completed', 'Incomplete'],
                    datasets: [{
                        data: [parseInt(dataJSON.completedcourses), (parseInt(dataJSON.assignedcourses) - parseInt(dataJSON.completedcourses))],
                        backgroundColor: ['#4a98bb', '#eaeaea'],
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
    }, 200);
}


function loadCourseGraph(Y, d) {
    var dataJSON = JSON.parse(d);
    var chartData = [];
    var chartLabels = [];

    setTimeout(function () {
        if (dataJSON != null) {
            for (var i = 0; i < dataJSON.length; i++) {
                chartLabels.push(dataJSON[i].coursename);
                chartData.push(parseInt(dataJSON[i].users));
            }

            new Chart(document.getElementById('CourseChart').getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    datasets: [{
                        data: chartData,
                        backgroundColor: '#303e83'
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Top Course Enrolments'
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
    }, 200);
}


function loadDateGraph(Y, d) {
    var dataJSON = JSON.parse(d);
    var lineMonths = [];
    var lineData1 = [];
    var lineData2 = [];

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
        $.each(dataJSON, function (k, d) {
            if (months[d.month-1] == lineMonths[i]) {
                lineData1.push(parseInt(d.logins));
                lineData2.push(parseInt(d.accesses));
            }
        });
    }  

    var datasets = [{
        label: 'User Logins',
        data: lineData1.reverse(),
        backgroundColor: '#57c5d7',
        borderColor: '#57c5d7',
        fill: false
    },
    {
        label: 'Course Access',
        data: lineData2.reverse(),
        backgroundColor: '#3d6b9f',
        borderColor: '#3d6b9f',
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
                    text: 'Staff Activity (last 12 months)',
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
    }, 1000);
}