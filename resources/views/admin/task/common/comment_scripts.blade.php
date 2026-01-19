<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>
<script src="{{asset('assets/js/imageuploadify.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
    $(document).ready(function (e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.commentForm').hide();

        $('.error').hide();

        $('.list').hide();

        $('.replyicon').hide();

        $("#mention").select2({
            placeholder: "mention"
        });

        $('#createComment').on('click',function(e){

            $('.d-none').removeClass('d-none');
            $('.commentListing').removeClass('d-none');
            // $('#cmntReply').hide();
            $('#description').val('');
            $('#mention').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#mention").select2({
                placeholder: "@lang('index.mention')"
            });
            let text = $(this).text();
            (text === 'Comment') ? $(this).text('Close') : $(this).text("@lang('index.comment')");

            $('.commentForm').toggle(500);
            $('.list').toggle(500);
        })

        $('.showComments').click(function(e){
            $('.d-none').removeClass('d-none');
            $('.commentListing').removeClass('d-none');
            // $('#cmntReply').hide();
            $('#description').val('');
            $('#mention').select2('destroy').find('option').prop('selected', false).end().select2();
            $("#mention").select2({
                placeholder: "@lang('index.mention')"
            });
            let text = $('#createComment').text();
            (text === 'Comment') ? $('#createComment').text('Close') : $('#createComment').text("@lang('index.comment')");

            $('.commentForm').toggle(500);
            $('.list').toggle(500);
        })

        $('body').on('click','#showReply',function(e){
            e.preventDefault();
            let id = $(this).data('id')
            $('.reply'+id+'').toggle(500);
            $('.reply'+id+'').removeClass('d-none');
        });

        $('body').on('click','.replyCreate',function(e){
            e.preventDefault();
            let commentId = $(this).data('comment');
            $('#commentId').val(commentId);
            $('#description').attr("placeholder", "@lang('index.reply')");
            $('.replyicon').show();
            $('html,body').animate({
                scrollTop: $("#replyForm").offset().top - 100
            }, 300);
        });

        $('.replyicon').click(function(e){
            e.preventDefault();
            $('#commentId').val('');
            $('#description').val("");
            $('#description').attr("placeholder", "@lang('index.write_comment')");
            $(this).hide();
        });

        $('body').on('click','#commentSubmit',function(e){
            e.preventDefault()
            let url = $('#taskCommentForm').attr('action');;
            let formData =  $('#taskCommentForm').serialize();
            $.ajax({
                type: "POST",
                url: url,
                data: formData
            }).done(function(response) {
                if(response.status_code == 200 && response.data != ''){
                    let commentDetail = response.data;
                    let id = commentDetail.id;
                    let commentId = commentDetail.comment_id;
                    let avatar= commentDetail.avatar;
                    let createdBy= commentDetail.created_by;
                    let createdAt= commentDetail.created_at;
                    let description = commentDetail.description;
                    let commentDeleteRoute = "{{ url('admin/task-comment/delete') }}" + '/' + id
                    let replyDeleteRoute = "{{url('admin/task-comment/reply/delete')}}" + '/' + id;
                    let mentioned = commentDetail.mentioned;

                    $('#description').val('');
                    $('#mention').select2('destroy').find('option').prop('selected', false).end().select2();
                    $("#mention").select2({
                        placeholder: "@lang('index.mention')"
                    });
                    $('#commentId').val('');
                    if(commentDetail.comment_id == ''){
                        let spanId = 'comment'+id;
                        let commentCount = $('.commentsCount').text();
                        let totalComments = parseInt(commentCount) + 1;
                        $('.commentsCount').text(totalComments)
                        let count = 0;

                        $('<div class="comment-box parentComment'+id+'">'+
                            '<div class="comment-image text-center mt-2">'+
                            '<img class="rounded-circle checklist-image" style="object-fit: cover" title="'+createdBy+'"  src="'+avatar+'" alt="profile">'+
                            '</div>'+

                            '<div class="comment-content rounded w-100">'
                            +'<h5 class="mb-1">'+ createdBy +'</h5>'+
                            '<p class="comment-date text-muted">'+createdAt +'</p>'+
                            '<p class="comment" id="'+spanId+'">'
                            + description +
                            '</p>'+
                            '<div class="comment-reply position-relative commentReply'+id+'">'+

                            '<div class="row number-reply d-flex align-items-center justify-content-between">'+

                            '<div class="col-lg-6">'+
                            '<p class="text-muted pt-1" id="showReply" data-id="'+id+'">'+
                            '<span class="replyCount'+id+'">'+count+ '</span>'+ ' reply'+
                            '</p>'+
                            '</div>'+

                            '<div class="col-lg-6">'+
                            '<button data-mention="'+createdBy+'" data-comment="'+id+'" class="replyCreate btn btn-secondary btn-xs float-end">'+

                            '@lang('index.reply')</button>'+
                            '</div>'+

                            '</div>'+

                            '<div class="reply'+id+'" id="cmntReply">'+

                            '</div>'+
                            '</div>'+
                            '<a class="commentDelete" data-comment="'+id+'" id="deleteComment" data-id="'+id+'" data-title="Comment" href="'+commentDeleteRoute+'">'+
                            '<i class="link-icon fst-normal" data-feather="x">'+'x</i>'+
                            '</a>'+
                            '</div>'+
                            '</div>')
                            .appendTo(".commentsAdd");

                        if(mentioned.length > 0){
                            mentioned.forEach(function(data) {
                                let name = "@"+data.name+ " " ;
                                $('#comment'+id+'').prepend('<span">'+ '<a href="#">'+ name +'</a>'+ '</span>');
                            });
                        }

                        $('html,body').animate({
                            scrollTop: $('#comment'+id+'').offset().top - 100
                        }, 300);

                    }else{
                        let spanReplyId = 'reply'+id;
                        let repliesCount = $('.replyCount'+commentId+'').text();
                        let totalReplies = parseInt(repliesCount) + 1;
                        $('.replyCount'+commentId+'').text(totalReplies);
                        $(
                            '<div class="comment-box ps-4 mt-2 singleReply'+id+'">'+
                            '<div class="comment-image text-center mt-2">'+
                            '<img class="rounded-circle checklist-image" style="object-fit: cover" title="'+createdBy+'" src="'+avatar+'"  alt="profile">'+
                            '</div>'+
                            '<div class="comment-content rounded w-100 bg-white">'+
                            ' <h5 class="mb-1">'+ createdBy +' </h5>'+
                            '<p class="comment-date text-muted">'+createdAt +'</p>'+
                            '<p class="comment" id="'+spanReplyId+'">'
                            + description +
                            '</p>'+
                            '<a class="replyDelete" id="deleteComment" data-title="Reply" data-comment="'+commentId+'" data-id="'+id+'" href="'+replyDeleteRoute+'">'+
                            '<i class="link-icon fst-normal" data-feather="x">'+'x</i>'+
                            '</a>'+
                            '</div>'+
                            '</div>'
                        ).appendTo(".reply"+commentId+"");

                        if(mentioned.length > 0){
                            mentioned.forEach(function(data) {
                                let name = "@"+data.name+ " " ;
                                $('#reply'+id+'').prepend('<span">'+ '<a href="#">'+ name +'</a>'+ '</span>');
                            });
                        }

                        $('html,body').animate({
                            scrollTop: $('.replyCount'+commentId+'').offset().top - 60
                        }, 300);
                    }
                    $('#commentId').val('');
                    $('#description').val("");
                    $('#description').attr("placeholder", "@lang('index.write_comment')");
                    $('.replyicon').hide();
                }
            }).error(function(error){
                let errorMessage = error.responseJSON.message;
                $('html,body').animate({
                    scrollTop: $("#showFlashMessageResponse").offset().top - 70
                }, 300);
                $('#errorMessageDelete').removeClass('d-none');
                $('.error').show();
                $('.errorMessageDelete').text(errorMessage);
                $('div.alert.alert-danger').not('.alert-important').delay(1000).slideUp(900);
            });
        });

        $('body').on('click', '#deleteComment', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let id = $(this).data('id');
            let url = $(this).attr('href');
            let commentId = $(this).data('comment');
            Swal.fire({
                title: @json(__('index.delete_confirm', ['title' => ''])) + ' ' + title,
                showDenyButton: true,
                confirmButtonText: '@lang('index.yes')',
                denyButtonText: '@lang('index.no')',
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'GET',
                        url: url,
                    }).done(function(response) {
                        if(response.status_code == 200){
                            if(title == 'Reply'){
                                let repliesCount = $('.replyCount'+commentId+'').text();
                                let totalReplies = parseInt(repliesCount) - 1;
                                $('.replyCount'+commentId+'').text(totalReplies);
                                $('.singleReply'+id+'').remove();
                            } else {
                                let commentCount = $('.commentsCount').text();
                                let totalComments = parseInt(commentCount) - 1;
                                $('.commentsCount').text(totalComments);
                                $('.parentComment'+id+'').remove();
                            }
                        }
                    }).fail(function(xhr) {
                        let errorMessage = xhr.responseJSON?.message || 'something_went_wrong';
                        $('html,body').animate({
                            scrollTop: $("#showFlashMessageResponse").offset().top - 70
                        }, 300);
                        $('#errorMessageDelete').removeClass('d-none');
                        $('.error').show();
                        $('.errorMessageDelete').text(errorMessage);
                        $('div.alert.alert-danger').not('.alert-important').delay(1000).slideUp(900);
                    });

                }
            })
        })
    });

</script>
