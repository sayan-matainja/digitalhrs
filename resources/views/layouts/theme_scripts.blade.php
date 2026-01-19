<script>
    {{--$(document).ready(function () {--}}

    {{--    let theme = '{{\App\Helpers\AppHelper::getTheme()}}'--}}
    {{--    if(theme === 'dark'){--}}
    {{--       $('#sun').show();--}}
    {{--       $('#moon').hide();--}}
    {{--    }else{--}}
    {{--       $('#moon').show();--}}
    {{--       $('#sun').hide();--}}
    {{--    }--}}

    {{--    $('#moon').click(function(){--}}
    {{--        changeTheme();--}}
    {{--        $('head').append(--}}
    {{--            '<link rel="stylesheet"  \--}}
    {{--             href="{{asset('assets/css/style_dark.css')}}" id="themeColor" />');--}}
    {{--        $('#sun').show();--}}
    {{--        $('#moon').hide();--}}
    {{--    })--}}

    {{--    $('#sun').click(function(){--}}
    {{--        changeTheme()--}}
    {{--        $('head').append(--}}
    {{--            '<link rel="stylesheet"  \ ' +--}}
    {{--            'href="{{asset('assets/css/style.css')}}" id="themeColor" />');--}}
    {{--        $('#moon').show();--}}
    {{--        $('#sun').hide();--}}
    {{--    })--}}

    {{--    function changeTheme(){--}}
    {{--        $.ajax({--}}
    {{--            type: "GET",--}}
    {{--            url: "{{route('admin.app-settings.change-theme')}}",--}}
    {{--            success: function(data){--}}
    {{--                $("#themeColor").remove();--}}
    {{--            }--}}
    {{--        });--}}
    {{--    }--}}
    {{--});--}}

    $(document).ready(function () {
        let theme = '{{ \App\Helpers\AppHelper::getTheme() }}';
        loadTheme(theme);

        $('#moon').click(function() {
            changeTheme();
        });

        $('#sun').click(function() {
            changeTheme();
        });

        function changeTheme() {
            $.ajax({
                type: "GET",
                url: "{{ route('admin.change-theme') }}",
                success: function(data) {
                    $("#themeColor").remove();
                    loadTheme(data.theme);
                    location.reload();
                }
            });
        }

        function loadTheme(theme) {
            if (theme === 'light') {
                $('head').append('<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="themeColor" />');
                $('#moon').show();
                $('#sun').hide();
            } else {
                $('head').append('<link rel="stylesheet" href="{{ asset('assets/css/style_dark.css') }}" id="themeColor" />');
                $('#sun').show();
                $('#moon').hide();
            }
        }
    });

</script>
