@extends('web.layouts.app')

@section('title', 'EXPIRED')

@section('content')
<div class="container pt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3 text-center">
            <img width="180px" class="d-block mx-auto mb-3" src="{{ asset('images/gm-logo-color.png') }}" alt="GRABMAID" />
            <div class="alert alert-danger">Password Reset Expired</div>
            <h3 class="card-title">Reset Password session is expired.</h3>
            <p><a href="http://www.grabmaid.my/">Back to home</a></p>
        </div>
    </div>
</div>
@endsection
