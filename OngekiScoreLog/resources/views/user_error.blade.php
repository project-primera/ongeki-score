@extends('layouts.app')

@section('title', "存在しないユーザーページです")

@section('content')
    <div class="box">
        <p>このユーザーはOngekiScoreLogに登録していますが、オンゲキNETからスコア取得を行っていません。(UserID: {{$id}})</p>
        <p>スコアの取得方法は<a href="/howto">こちら</a>をお読みください。</p>
    </div>
@endsection