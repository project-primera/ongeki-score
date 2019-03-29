@extends('layouts.app')
@section('title', "パスワードをリセット")
@section('hero_title', "パスワードをリセット")

@section('content')
    <article class="box">
        <form method="POST" action="{{ route('password.request') }}" aria-label="パスワードをリセット">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
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
                    <input id="password" type="password" class="input{{ $errors->has('password') ? ' is-danger' : '' }}" name="password" required>
                    @if ($errors->has('password'))
                        <div class="notification is-danger">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="field">
                <label for="password-confirm" class="label">確認用パスワード</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input id="password-confirm" type="password" class="input" name="password_confirmation" required>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-link">パスワードをリセットする</button>
                </div>
            </div>
        </form>
    </article>
@endsection
