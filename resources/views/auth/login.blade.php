@extends('layouts.app')
@section('body_class')
@section('page')
    <div class="container">
        <div class="row">

            <!-- Mixins-->
            <!-- Pen Title-->
            <div class="pen-title">
                <img src="{{ asset('images/logo.png') }}" alt="..." class="">
            </div>
            <div class="container">
                <div class="card"></div>
                <div class="card">
                    <h1 class="title">{{ __('views.auth.login.login') }}</h1>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="input-container">
                            <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                            @endif
                            <label for="Username">{{ __('views.auth.login.username') }}</label>
                            <div class="bar"></div>
                        </div>
                        <div class="input-container">
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif
                            <label for="Password">{{ __('views.auth.login.password') }}</label>
                            <div class="bar"></div>
                        </div>
                        <div class="button-container">
                            <button type="submit"><span>{{ __('views.auth.login.acept') }}</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
@section('styles')
    @parent
    {{ Html::style(mix('assets/admin/css/login.css')) }}
@endsection
@endsection