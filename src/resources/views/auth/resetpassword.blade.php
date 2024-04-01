@extends('layouts.landing')

@section('content')
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
        @if($failed)
        <div class="alert alert-dark-danger alert-dismissible fade show">
            Link reset password keliru, silahkan lakukan reset password ulang.
        </div>
        <a href="{{route('forgotpassword')}}" class="btn btn-primary btn-block">
            {{__('auth.login.forgotpassword')}}
        </a>
        @elseif($expired)
        <div class="alert alert-dark-danger alert-dismissible fade show">
            Link reset password telah kadaluarsa, silahkan lakukan reset password ulang.
        </div>
        <a href="{{route('forgotpassword')}}" class="btn btn-primary btn-block">
            {{__('auth.login.forgotpassword')}}
        </a>
        @else
        <!-- Form -->
        <form method="POST" action="<?= route('resetPassword', ['verifyCode' => $verifyCode, 'email' => $email]); ?>">
            {{ csrf_field() }}
            @if($setNewPassword)
            <h5 class="text-center font-weight-bold mb-4">Set Password</h5>
            <hr class="mt-0 mb-4">
            <p>
                Silahkan set password untuk bisa login ke akun Anda.
            </p>
            @else
            <h5 class="text-center font-weight-bold mb-4">Reset Password</h5>
            <hr class="mt-0 mb-4">
            <p>
                Untuk melanjutkan proses reset password, silahkan isi password baru yang Anda inginkan.
            </p>
            @endif
            
            @include('component.alert')
            
            <div class="form-group">
                <label class="form-label">User</label>
                <input type="text" class="form-control" readonly value="{{$user['name']}}">
            </div>
            <hr>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" class="form-control" placeholder="" name="password" maxlength="250">
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" placeholder="" name="password_confirmation" maxlength="250">
            </div>

            <button class="btn btn-primary btn-block">Submit</button>
            
        </form>
        <!-- / Form -->
        @endif

    </div>
</div>
<div class="text-center p-4">
    {{ config('AppConfig.system.template.admin.footer.text') }}
</div>
@endsection