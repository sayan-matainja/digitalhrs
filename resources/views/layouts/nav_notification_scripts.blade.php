
<script>
    $('document').ready(function (){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });



        $('#notificationsNavBar').click(function(event){
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {
                let len = 0;
                if(data.data != null){
                    len = data.data.length;
                }
                if(len > 0) {
                    $(".check").remove();
                    for (let i = 0; i < len; i++) {
                        let title = data.data[i].title
                        let publishDate = data.data[i].publish_date;
                        let notification =
                            "<span class='dropdown-item d-flex align-items-center py-2 check'>"+
                            "<div class='wd-30 ht-30 d-flex align-items-center justify-content-center bg-primary rounded-circle me-3'>"+
                            "<i class='icon-bell text-white' data-feather='bell'>"+"</i>"+
                            "</div>"+

                            "<div class='text-muted  me-2'>"+
                            "<p>" +title+ "</p>"+
                            "<p class='tx-12 text-muted publish_date'>" +publishDate+ "</p>"+
                            "</div>"+
                            "</span>";
                        $("#notifications-detail").append(notification);
                    }
                }

            });
        })

        $('#navAdminNotificationCreate').on('click',function(event){
            let href = $(this).data('href');
            window.location.href = href;
        });

        $('#navAdminNotificationList').on('click',function(event){
            let url = $(this).data('href');
            window.location.href = url;
        });




    });
</script>
