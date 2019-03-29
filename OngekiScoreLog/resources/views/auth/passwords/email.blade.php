@extends('layouts.app')
@section('title', "パスワードをリセット")
@section('hero_title', "パスワードをリセット")

@section('content')
    <article class="box">
        @if (session('status'))
            <div class="notification is-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" aria-label="パスワードをリセット">
            @csrf
            <div class="field">
                <label for="email" class="label">メールアドレス</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input id="email" type="email" class="input{{ $errors->has('email') ? ' is-danger' : '' }}" name="email" value="{{ old('email') }}" required>
                    @if ($errors->has('email'))
                        <div class="notification is-danger">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-link">パスワードリセット用のリンクを送信する</button>
                </div>
            </div>
        </form>
    </article>
@endsection
