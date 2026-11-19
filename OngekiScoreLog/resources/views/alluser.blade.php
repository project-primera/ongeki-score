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

        $searching = $condition->keyword() !== null;

        // 「11,593 人中 101〜200 人目」。該当が無いページ（最終ページより先）では人数だけ出す
        $count = number_format($users->total()) . ' 人';
        if ($users->isNotEmpty()) {
            $count .= '中 ' . number_format($users->firstItem()) . '〜' . number_format($users->lastItem()) . ' 人目';
        }
        if ($searching) {
            $count = '検索結果 ' . $count;
        }

        $clearQuery = http_build_query($condition->queryWithoutKeyword());
    @endphp

    <article class="box">
        {{-- 検索すると 1 ページ目に戻る。並び替えている場合は、その並びのまま検索する --}}
        <form method="get" action="/user" class="user-list-search">
            @if ($condition->isSorted())
                <input type="hidden" name="sort" value="{{$condition->sort()}}">
                <input type="hidden" name="order" value="{{$condition->order()}}">
            @endif
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input class="input" type="search" name="q" value="{{$condition->keyword()}}" maxlength="{{\App\OngekiScoreLog\UserListCondition::KEYWORD_MAX_LENGTH}}" placeholder="名前で検索" aria-label="名前で検索">
                </div>
                <div class="control">
                    <button class="button is-info" type="submit">検索</button>
                </div>
            </div>
        </form>

        @if ($searching)
            <p class="user-list-clear"><a href="/user{{$clearQuery === '' ? '' : '?' . $clearQuery}}" rel="nofollow">検索を解除</a></p>
        @endif

        @if ($users->total() > 0)
            <p class="user-list-count">{{$count}}</p>
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
