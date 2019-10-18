@extends('web.layouts.app')

@section('title', 'RESET PASSWORD')

@section('content')
<div class="container pt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <img width="180px" class="d-block mx-auto mb-3" src="{{ asset('images/gm-logo-color.png') }}" alt="GRABMAID" />
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Reset Password</h3>
                    <p class="badge badge-pill badge-secondary">{{ $email }}</p>
                    <form action="{{ route('password.reset.update', $token) }}" method="post">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                        </div>
                        <button class="btn btn-success float-right" type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
