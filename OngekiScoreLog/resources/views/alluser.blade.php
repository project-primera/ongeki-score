@extends('layouts.app')

@section('title', 'Page Title')
@section('sidemark_alluser', "is-active")
@section('hero_title', "すべてのユーザー")
@section('additional_footer')
    <script type="text/javascript" src="/js/sortAllUserTable.js"></script>
@endsection

@section('content')
<div class="container">
    <article class="box">
        <div id="sort_table" class="table_wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="sort" data-sort="sort_id">ID</th>
                        <th class="sort" data-sort="sort_name">Name</th>
                        <th class="sort" data-sort="sort_trophy">Trophy</th>
                        <th class="sort" data-sort="sort_lv">Lv</th>
                        <th class="sort" data-sort="sort_rating">Rating</th>
                        <th class="sort" data-sort="sort_max">(Max)</th>
                        <th class="sort" data-sort="sort_bp">BP</th>
                        <th class="sort desc" data-sort="sort_update">Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="sort" data-sort="sort_id">ID</th>
                        <th class="sort" data-sort="sort_name">Name</th>
                        <th class="sort" data-sort="sort_trophy">Trophy</th>
                        <th class="sort" data-sort="sort_lv">Lv</th>
                        <th class="sort" data-sort="sort_rating">Rating</th>
                        <th class="sort" data-sort="sort_max">(Max)</th>
                        <th class="sort" data-sort="sort_bp">BP</th>
                        <th class="sort desc" data-sort="sort_update">Update</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @foreach ($users as $item)
                        <tr>
                            <td class="sort_id">{{$item->user_id}}</td>
                            <td class="sort_name"><a href="/user/{{$item->user_id}}">{{$item->name}}</a></td>
                            <td class="sort_trophy">{{$item->trophy}}</td>
                            <td class="sort_lv">{{$item->level}}</td>
                            <td class="sort_rating">{{$item->rating}}</td>
                            <td class="sort_max">{{$item->rating_max}}</td>
                            <td class="sort_bp">{{$item->battle_point}}</td>
                            <td class="sort_update">{{date('Y-m-d', strtotime($item->updated_at))}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </article>
</div>
@endsection
