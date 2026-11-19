@extends('layouts.app')

@section('title', 'すべてのユーザー')
@section('sidemark_alluser', "is-active")
@section('hero_title', "すべてのユーザー")

@section('content')
    @php
        // 並び替えのキー（?sort= の値）=> 列見出し
        $columns = [
            'id' => 'ID',
            'name' => 'Name',
            'trophy' => 'Trophy',
            'lv' => 'Lv',
            'rating' => 'Rating',
            'max' => '(Max)',
            'bp' => 'BP',
            'update' => 'Update',
        ];
    @endphp

    <article class="box">
        @if ($users->total() > 0)
            <p class="user-list-count">
                @if ($users->isNotEmpty())
                    {{number_format($users->total())}} 人中 {{number_format($users->firstItem())}}〜{{number_format($users->lastItem())}} 人目
                @else
                    {{number_format($users->total())}} 人
                @endif
            </p>
        @endif

        {{ $users->links('layouts.components.pagination') }}

        @if ($users->isEmpty())
            <p class="user-list-empty">該当するユーザーがいません</p>
        @else
            <div class="table_wrap">
                <table class="table">
                    {{-- 列見出しは並び替えのリンク。現在の列には向きの印（.sort.asc / .sort.desc の ▲▼）が付く --}}
                    <thead>
                        <tr>
                            @foreach ($columns as $sort => $label)
                                <th class="{{trim('sort ' . $condition->orderOf($sort))}}"><a href="/user?{{http_build_query($condition->sortQuery($sort))}}" rel="nofollow">{{$label}}</a></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            @foreach ($columns as $sort => $label)
                                <th class="{{trim('sort ' . $condition->orderOf($sort))}}"><a href="/user?{{http_build_query($condition->sortQuery($sort))}}" rel="nofollow">{{$label}}</a></th>
                            @endforeach
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
        @endif

        {{ $users->links('layouts.components.pagination') }}
    </article>
@endsection
