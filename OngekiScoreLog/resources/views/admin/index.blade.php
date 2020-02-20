@extends('layouts.app')

@section('title', '管理ページ')
@section('hero_title', "管理ページ")
@section('hero_subtitle', "Top")

@section('submenu')
    @include('admin/_submenu', ['active' => 'index'])
@endsection

@section('content')
    <article class="box">
    </article>
@endsection