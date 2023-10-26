@extends('layouts.email.app')

@section('content')
<tr>
	<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
		<div style="font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;font-size:14px;line-height:22px;text-align:left;color:#525252;">
            {!! __('auth.profile.otp_email.message',['otp'=>$otp]) !!}
		</div>
	</td>
</tr>
@endsection
