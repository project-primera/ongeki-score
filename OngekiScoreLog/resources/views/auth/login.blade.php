@extends('layouts.app')
@section('title', "ログイン")
@section('hero_title', "ログイン")

@section('content')
    <article class="box">
        <form method="POST" action="{{ route('login') }}" aria-label="ログイン">
            @csrf
            <div class="field">
                <label for="email" class="label">メールアドレス</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input id="email" type="email" class="input{{ $errors->has('email') ? ' is-danger' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                    @if ($errors->has('email'))
                        <div class="notification is-danger">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="field">
                <label for="password" class="label">パスワード</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input id="password" type="password" class="input{{ $errors->has('password') ? ' is-danger' : '' }}" name="password" required autofocus>
                    @if ($errors->has('password'))
                        <div class="notification is-danger">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <label class="checkbox">
                        <input type="checkbox"  name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>&nbsp;ログイン状態を保存する
                    </label>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-link">ログイン</button>
                </div>
            </div>
        </form>
        <div style="padding-top: 1.5em;">
            <a href="{{ route('password.request') }}">&nbsp;パスワードを忘れた場合はこちら
            </a>
        </div>
    </article>
@endsection
