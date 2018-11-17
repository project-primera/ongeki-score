@extends('layouts.app')

@section('title', "ツイート結果")
@section('hero_title', "ツイート結果")

@section('content')
    <article class="box">
        {{$result}}
    </article>
@endsection