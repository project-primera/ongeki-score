@extends('layouts.app')
@section('title', "新規登録")
@section('hero_title', "新規登録")

@section('content')
    <article class="box">
        <form method="POST" action="{{ route('register') }}" aria-label="新規登録">
            @csrf
            <div class="field">
                <label for="name" class="label">ユーザー名</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-user-circle"></i>
                    </span>
                    <input id="name" type="text" class="input{{ $errors->has('name') ? ' is-danger' : '' }}" name="name" value="{{ old('name') }}" required autofocus>
                    @if ($errors->has('name'))
                        <div class="notification is-danger">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>
            </div>
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
                <label for="password-confirm" class="label">確認用パスワード</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input id="password-confirm" type="password" class="input" name="password_confirmation" required autofocus>
                </div>
            </div>
            <hr>
            <div class="field">
                <label for="private" class="label">
                <input id="private" type="checkbox" name="private" >
                    プライベートモードを有効にする
                </label>
            </div>
            <p>
                プライベートモードにすると自分のユーザーページが他のユーザーから閲覧できなくなります。
            </p>
            <ul>
                <li>・「すべてのユーザー」に表示されなくなります</li>
                <li>・スコアページやレーティングページ、獲得称号ページが自分以外のユーザーから見えなくなります</li>
                <li>・副作用として、OngekiScoreLogのデータを利用した外部サイトが利用できなくなるかもしれません</li>
                <li>※統計情報の集計対象からは外れません</li>
                <li>※管理者はユーザーサポートのためプライベートモードのユーザーページにアクセスする可能性があります</li>
            </ul>
            <hr>
            <p>
                以下を確認の上、同意する場合のみご登録ください。<br>
                ・<a href="/eula">利用規約 / プライバシーポリシー</a><br>
                ・<a href="/data-policy">データの二次利用ポリシー</a>
            </p>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-link">登録</button>
                </div>
            </div>
        </form>
    </article>
@endsection
