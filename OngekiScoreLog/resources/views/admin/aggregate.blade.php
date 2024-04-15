@extends('layouts.app')

@section('title', '管理ページ')
@section('hero_title', "管理ページ")
@section('hero_subtitle', "config")

@section('submenu')
    @include('admin/_submenu', ['active' => 'aggregate'])
@endsection

@section('content')
    <article class="box">
        <h3 class="title is-3">集計一覧</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>max</th>
                    <th>updated_at</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $key => $value)
                    <tr>
                        <td>{{$value->id}}</td>
                        <td>{{$value->max}}</td>
                        <td>{{$value->updated_at}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </article>
@endsection
