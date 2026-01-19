<script>
    $('document').ready(function(){

        $('.errorStartWorking').hide();

        $('.errorStopWorking').hide();

        $('.successStartWorking').hide();

        $('.successStopWorking').hide();

        function showLoader() {
            $('#loader').show();
        }

        function hideLoader() {
            $("#loader").hide();
        }

        setInterval(drawClock, 1000);

        function drawClock(){
            let now = new Date();
            let hr = now.getHours();
            let min = now.getMinutes();
            let sec = now.getSeconds();
            let hr_rotation = 30 * hr + min / 2;
            let min_rotation = 6 * min;
            let sec_rotation = 6 * sec;
            hour.style.transform = `rotate(${hr_rotation}deg)`;
            minute.style.transform = `rotate(${min_rotation}deg)`;
            second.style.transform = `rotate(${sec_rotation}deg)`;

            // display weekday and date
            // const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            // const weekday = weekdays[now.getDay()];
            // const date = now.toLocaleDateString();
            //
            // const dateDiv = document.getElementById('date');
            // dateDiv.innerText = `${weekday}, ${date}`;
        }

        let tasksChart = new Chart(document.getElementById("tasksChart"), {
            type: 'pie',
            data: {
                labels: [ translatedStrings.pending,
                    translatedStrings.on_hold,
                    translatedStrings.in_progress,
                    translatedStrings.completed,
                    translatedStrings.cancelled
                ],
                datasets: [{
                    label: 'Task state',
                    type: 'doughnut',
                    backgroundColor: ["#7ee5e5","#f77eb9","#4d8af0","#00ff00","#FF0000"],
                    borderColor: [
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)',
                        'rgba(256, 256, 256, 1)'
                    ],

                    data: [
                        {{$taskPieChartData['not_started']}},
                        {{$taskPieChartData['on_hold']}},
                        {{$taskPieChartData['in_progress']}},
                        {{$taskPieChartData['completed']}},
                        {{$taskPieChartData['cancelled']}}
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                        text: 'Task Pie Chart'
                    }
                }
            }
        });

        let ctx = document.getElementById('projectChart')?.getContext('2d');
        let labels = [
            translatedStrings.pending,
            translatedStrings.on_hold,
            translatedStrings.in_progress,
            translatedStrings.completed,
            translatedStrings.cancelled
        ];
        let barColors = ["#7ee5e5","#f77eb9","#4d8af0","green",'red'];
        let barData = [
            {{$projectCardDetail['not_started']}},
            {{$projectCardDetail['on_hold']}},
            {{$projectCardDetail['in_progress']}},
            {{$projectCardDetail['completed']}},
            {{$projectCardDetail['cancelled']}}
        ];

        let myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels ,
                datasets: [{
                    label: 'Project',
                    backgroundColor: barColors,
                    data: barData,
                    borderWidth: 1,
                    borderRadius: 10,
                    borderSkipped: true,
                }],

            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                },
                plugins: {
                    legend: {
                        position: 'none',
                    },
                    title: {
                        display: false,
                        text: 'Project Bar Chart'
                    }
                },
                barThickness: 50,

            }
        });

        $("#startWorkingBtn").click(function(e) {
            e.preventDefault();
            showLoader();
            let url = $(this).attr('href');
            let audioUrl = $(this).data('audio');

            getLocation().then(function (position) {
                let params = {
                    lat: position.latitude,
                    long: position.longitude
                };
                let queryString = $.param(params);
                let urlWithParams = url + "?" + queryString
                $.ajax({
                    type: "get",
                    url: urlWithParams,
                    success: function (response) {
                        $('#startWorkingBtn').addClass('d-none');
                        $('#checkInTime').text(response.data.check_in_at);
                        $('#flashAttendanceMessage').removeClass('d-none');
                        $('.successStartWorking').show();
                        $('.successStartWorkingMessage').text(response.message);
                        $('div.alert.alert-success').not('.alert-important').delay(500).slideUp(900);
                        let audio = new Audio(audioUrl);
                        audio.play();
                        location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status === 400) {
                            let errorObj = JSON.parse(jqXHR.responseText);
                            let errorMessage = "Error: " + errorObj.message;
                            $('#flashAttendanceMessage').removeClass('d-none');
                            $('.errorStartWorking').show();
                            $('.errorStartWorkingMessage').text(errorMessage);
                            $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                        } else {
                            let errorMessage = "Error: " + errorThrown;
                            $('#flashAttendanceMessage').removeClass('d-none');
                            $('.errorStartWorking').show();
                            $('.errorStartWorkingMessage').text(errorMessage);
                            $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                        }
                        location.reload();
                    },
                    complete: function () {
                        hideLoader();
                    }
                });
            }).catch(function (error) {
                hideLoader();
                $('#flashAttendanceMessage').removeClass('d-none');
                $('.errorStartWorking').show();
                $('.errorStartWorkingMessage').text("Error occurred while retrieving location: "+error.message);
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            });
        });

        $("#stopWorkingBtn").click(function(e){
            e.preventDefault();
            showLoader();
            let url = $(this).attr('href');
            let audioUrl = $(this).data('audio');
            getLocation().then(function (position) {
                let params = {
                    lat: position.latitude,
                    long: position.longitude
                };
                let queryString = $.param(params);
                let urlWithParams = url + "?" + queryString

                $.ajax({
                    type: "get",
                    url: urlWithParams,
                success: function(response){
                    let audio = new Audio(audioUrl);
                    audio.play();
                    $('#stopWorkingBtn').addClass('d-none');
                    $('#checkOutTime').text(response.data.check_out_at);
                    $('#flashAttendanceMessage').removeClass('d-none');
                    $('.successStopWorking').show();
                    $('.successStopWorkingMessage').text(response.message);
                    $('div.alert.alert-success').not('.alert-important').delay(500).slideUp(900);
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status === 400) {
                        let errorObj = JSON.parse(jqXHR.responseText);
                        let errorMessage = "Error: " + errorObj.message;
                        $('#flashAttendanceMessage').removeClass('d-none');
                        $('.errorStopWorking').show();
                        $('.errorStopWorkingMessage').text(errorMessage);
                        $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                    } else {
                        let errorMessage = "Error: " + errorThrown;
                        $('#flashAttendanceMessage').removeClass('d-none');
                        $('.errorStopWorking').show();
                        $('.errorStopWorkingMessage').text(errorMessage);
                        $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
                    }
                    location.reload();
                },
                complete: function() {
                    hideLoader();
                }
            });
            }).catch(function (error) {
                hideLoader();
                $('#flashAttendanceMessage').removeClass('d-none');
                $('.errorStartWorking').show();
                $('.errorStartWorkingMessage').text("Error occurred while retrieving location: "+error.message);
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            });
        });

        function getLocation() {
            if (navigator.geolocation) {
                return new Promise(function(resolve, reject) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        let latitude = position.coords.latitude;
                        let longitude = position.coords.longitude;

                        resolve({ latitude: latitude, longitude: longitude });
                    }, function(error) {
                        reject(error);
                    });
                });
            } else {
                hideLoader();
                $('#flashAttendanceMessage').removeClass('d-none');
                $('.errorStartWorking').show();
                $('.errorStartWorkingMessage').text('Geolocation is not supported by this browser.');
                $('div.alert.alert-danger').not('.alert-important').delay(5000).slideUp(900);
            }
        }
    });



</script>
