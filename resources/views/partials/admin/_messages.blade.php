@if(\Illuminate\Support\Facades\Session::has('success'))
    <div class="alert alert-success alert-dismissible" role="alert" style="margin-top: 2%">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <strong>{{ \Illuminate\Support\Facades\Session::get('success') }}</strong>
    </div>
@endif

@if(\Illuminate\Support\Facades\Session::has('error'))
    <div class="alert alert-danger alert-dismissible" role="alert" style="margin-top: 2%">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <strong>{{ \Illuminate\Support\Facades\Session::get('error') }}</strong>
    </div>
@endif

