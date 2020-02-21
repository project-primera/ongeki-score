@extends('layouts.app')

@section('title', '管理ページ')
@section('hero_title', "管理ページ")
@section('hero_subtitle', "config")

@section('submenu')
    @include('admin/_submenu', ['active' => 'config'])
@endsection

@section('content')
    <article class="box">
        <h3 class="title is-3">config一覧</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>key</th>
                    <th>value</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>key</th>
                    <th>value</th>
                </tr>
            </tfoot>
            <tbody>
                @foreach ($result as $key => $value)
                    <tr>
                        <th>{{$key}}</th>
                        <td>{{$value}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </article>
@endsection