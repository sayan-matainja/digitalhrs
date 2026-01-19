
{{--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>--}}
{{--<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>--}}

<script>
    $(document).ready(function () {
        $('#nav-search').val('');
        let currentLI = 0;
        const keydown = 38;
        const keyup = 40;
        const keyEnter = 13;

        $("#nav-search").on("keyup", function (e) {
            e.preventDefault();
            let keycode = e.keyCode;

            if(keycode !== keydown && keycode !== keyup){
                let linksArr = {};
                $('.sidebar-menu li a').each(function (i) {
                    let href = $(this).data('href');
                    if(href !=='#'){
                        let name = $(this).text().toLowerCase();
                        linksArr[name] = $(this).data('href');
                    }
                });

                let linkKeys = Object.keys(linksArr)
                let searchQuery = $('#nav-search').val();
                if (searchQuery.length >= 0) {
                    $('#nav-search-listing li').remove();
                    if (searchQuery) {
                        for (i = 0; i < linkKeys.length; i++) {
                            let hreflocation = linksArr[linkKeys[i]];

                            if (linkKeys[i].toLowerCase().indexOf(searchQuery) > -1) {
                                $('#nav-search-listing').
                                append( '<li id="nav-menu" class="nav-listing list-group-item">' +
                                    '<a href=" '+hreflocation+' ">'+linkKeys[i]+'</a></li>');

                            }
                        }
                    }
                }
            }

            //for up down with arrow key highlight/select

            let listItems = document.querySelectorAll("#nav-search-listing li");
            listItems[currentLI].classList.add("highlight");
            if(keycode == keydown) {
                listItems[currentLI].classList.remove("highlight");
                currentLI = currentLI > 0 ? --currentLI : 0;
                console.log(currentLI);
                listItems[currentLI].classList.add("highlight");
            }

            if(keycode == keyup){
                listItems[currentLI].classList.remove("highlight");
                currentLI = currentLI < listItems.length - 1 ? ++currentLI : listItems.length - 1;
                listItems[currentLI].classList.add("highlight");
            }

            //redirect to highlighted url on enter key press
            if(keycode == keyEnter){
                let url = $('.highlight').find('a').attr('href');
                window.location.href = url;
            }

        });

        // ctrl + Q command for focusing in admin search field
        window.addEventListener("keydown",function (e) {
            if (e.ctrlKey && e.keyCode === 81){
                if($('#nav-search').is(":focus")) {
                    return true;
                } else {
                    e.preventDefault();
                    $('#nav-search').focus();
                }
            }
        });

        // toggle hide and show based on input field focus
        $(document).click(function(evt) {
            if ($(evt.target).closest('#admin-search-menu').length === 0) {
                $('.card-admin-search').hide();
            }
        });

        $('#admin-search-menu').focusin(function(e){
            e.preventDefault();
            $('.card-admin-search').show();
        });

    });


</script>
