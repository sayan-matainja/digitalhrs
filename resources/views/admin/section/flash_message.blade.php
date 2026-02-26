@if (isset($errors) && count($errors) > 0)
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif


@if (Session::has('success'))
    <div style="max-width: 100%" id="message"   class="d-flex justify-content-between alert alert-success {{Session::has('success_important') ? 'alert-important': ''}} ">

        @if(Session::has('success_important'))
        @endif
        {{session('success')}}
        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-bs-dismiss="alert" aria-hidden="true">×</button>
    </div>
@endif

@if (Session::has('danger'))
    <div style="max-width: 100%" id="message" class="d-flex justify-content-between alert alert-danger {{Session::has('danger_important') ? 'alert-important': ''}}">

        @if(Session::has('danger_important'))
        @endif
        {{session('danger')}}
        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-bs-dismiss="alert" aria-hidden="true">×</button>
    </div>
@endif

@if (Session::has('info'))
    <div style="max-width: 100%" id="message" class="d-flex justify-content-between alert alert-info {{Session::has('info_important') ? 'alert-important': ''}}">

        @if(Session::has('info_important'))
        @endif
        {{session('info')}}
        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-bs-dismiss="alert" aria-hidden="true">×</button>
    </div>
@endif

@if (Session::has('warning'))
    <div style="max-width: 100%" id="message" class="d-flex justify-content-between alert alert-warning {{Session::has('warning_important') ? 'alert-important': ''}}">

        @if(Session::has('warning_important'))
        @endif
        {{session('warning')}}
        <button type="button" style="color:white !important;opacity: 1 !important;" class="close bg-dark border-0 rounded-1" data-bs-dismiss="alert" aria-hidden="true">×</button>
    </div>
@endif
<script>
    setTimeout(function() {
        let elementToHide = document.getElementById('message');
        if (elementToHide) {
            elementToHide.style.display = 'none';
        }
    }, 2000);
</script>
