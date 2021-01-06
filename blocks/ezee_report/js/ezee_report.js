function loadPercentageGraph(Y, d) {
    var dataSummary = d;
    console.log(dataSummary);

    setTimeout(function () {
        if (dataSummary != null) {
            var percentage = (parseInt(dataSummary.completedcourses) / parseInt(dataSummary.totalcourses)) * 100;
            $('#ChartPercentage').text(percentage + '%');

            new Chart(document.getElementById('PercentageChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Completed', 'Incomplete'],
                    datasets: [{
                        data: [parseInt(dataSummary.completedcourses), (parseInt(dataSummary.totalcourses) - parseInt(dataSummary.completedcourses))],
                        backgroundColor: ['#241168', '#eaeaea'],
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    cutoutPercentage: 50,
                    title: {
                        display: true,
                        text: 'Team Completion'
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
    console.log(dataUser);

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
    console.log(dataDates);

    setTimeout(function () {
        new Chart(document.getElementById('DateChart').getContext('2d'), {
            type: 'line',

            data: {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
                datasets: [{
                        backgroundColor: '#57c5d7',
                        borderColor: '#57c5d7',
                        data: [0, 5, 5, 3, 6, 10, 11],
                        fill: false
                    },
                    {
                        backgroundColor: 'rgb(255, 99, 132)',
                        borderColor: 'rgb(255, 99, 132)',
                        data: [1, 2, 7, 9, 6, 5, 4],
                        fill: false
                    }
                ]
            },

            options: {
                title: {
                    display: true,
                    text: 'Recent Progress'
                },
                legend: {
                    display: false
                }
            }
        });
    }, 100);
}