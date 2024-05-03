@extends('layouts.landing')

@section('content')
@include('component.alert')
<div class="card">
    <div class="p-4 p-sm-5">

        <!-- Logo -->
        <div class="d-flex justify-content-center align-items-center mb-4">
            <a href="{{url('/')}}">
                <img src="{{$logo}}" alt="" style="max-width:200px;max-height:100px; height:auto">
            </a>
        </div>
        <!-- / Logo -->
        <h1 class="display-4 text-center">{{$coop_name}}</h1>
        @if($is_success)
        <div class="alert alert-dark-success alert-dismissible fade show">
            Reset password berhasil, silakan coba login kembali.
        </div>
        <a href="{{route('auth.login')}}" class="btn btn-default btn-block">
            {{__('auth.forgotpassword.label.login')}}
        </a>
        @else        
        <div class="alert alert-dark-danger alert-dismissible fade show">
            Reset password gagal : {{$message}}
        </div>
        <a href="{{route('forgotpassword')}}" class="btn btn-default btn-block">
            {{__('auth.login.forgotpassword')}}
        </a>
        @endif

    </div>
</div>
<div class="text-center p-4">
    {{ config('AppConfig.system.template.admin.footer.text') }}
</div>
@endsection