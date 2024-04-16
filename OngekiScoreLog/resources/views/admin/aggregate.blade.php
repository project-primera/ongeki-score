@extends('layouts.app')

@section('title', '管理ページ')
@section('hero_title', "管理ページ")
@section('hero_subtitle', "config")

@section('submenu')
    @include('admin/_submenu', ['active' => 'aggregate'])
@endsection

@section('additional_footer')
    <script type="text/javascript" src="{{ mix('/js/sortTable.js') }}"></script>
@endsection

@section('content')
    <article class="box">
        <h3 class="title is-3">集計一覧</h3>
        <div id="sort_table" class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th class="sort" data-sort="sort_key1">id</th>
                        <th>title</th>
                        <th>難易度</th>
                        <th class="sort" data-sort="sort_key2">max</th>
                        <th class="sort" data-sort="sort_key3">updated_at</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="sort" data-sort="sort_key1">id</th>
                        <th>title</th>
                        <th>難易度</th>
                        <th class="sort" data-sort="sort_key2">max</th>
                        <th class="sort" data-sort="sort_key3">updated_at</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @foreach ($result as $key => $value)
                        <tr>
                            <td class="sort_key1">{{$value->id}}</td>
                            <td>{{$musics[$value->song_id]}}</td>
                            <td>{{substr($difficultyToStr[$value->difficulty], 0, 3)}}</td>
                            <td class="sort_key2">{{$value->max}}</td>
                            <td class="sort_key3">{{$value->updated_at}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection
