@extends('layouts.app')

@section('title', 'すべてのユーザー')
@section('sidemark_alluser', "is-active")
@section('hero_title', "すべてのユーザー")

@section('content')
    <article class="box">
        <div class="table_wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Trophy</th>
                        <th>Lv</th>
                        <th>Rating</th>
                        <th>(Max)</th>
                        <th>BP</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Trophy</th>
                        <th>Lv</th>
                        <th>Rating</th>
                        <th>(Max)</th>
                        <th>BP</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($users as $item)
                        <tr>
                            <td>{{$item->user_id}}</td>
                            <td><a href="/user/{{$item->user_id}}">{{$item->name}}</a></td>
                            <td>{{$item->trophy}}</td>
                            <td>{{$item->level}}</td>

                            {{-- 現行バージョンになる前の更新は括弧付きで出す。並び替えの SQL と同じ比較にして、括弧の有無と並びがずれないようにする --}}
                            @if ($currentVersionFrom === null || $item->updated_at >= $currentVersionFrom)
                                <td>{{number_format((float) $item->rating, 3, '.', '')}}</td>
                                <td>{{number_format((float) $item->rating_max, 3, '.', '')}}</td>
                            @else
                                <td>({{number_format((float) $item->rating, 3, '.', '')}})</td>
                                <td>({{number_format((float) $item->rating_max, 3, '.', '')}})</td>
                            @endif

                            <td>{{$item->battle_point}}</td>
                            <td>{{substr((string) $item->updated_at, 0, 10)}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection
