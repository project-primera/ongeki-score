@extends('layouts.app')

@section('title', $status[0]->name . "の更新差分")
@section('hero_subtitle', $status[0]->trophy)
@section('hero_title', $status[0]->name)
@section('additional_footer')
    <script type="text/javascript" src="/js/html2canvas.min.js"></script>
    <script type="text/javascript" src="{{ mix('/js/userProgress.js') }}"></script>
@endsection
@if(!is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li class="is-active"><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection


@section('content')
    <article class="box">
        <h3 class="title is-3">新曲枠 レーティング対象曲</h3>
        <div id="sort_table" class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @for ($i = 0; $i < $statistics->newBestRatingCount; $i++)
                        <tr>
                            <td>{{$newScore[$i]->title}}</td>
                            <td>{{substr($newScore[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{{$newScore[$i]->level_str}}</td>
                            <td>{{number_format($newScore[$i]->technical_high_score)}}</td>
                            <td>{!!$newScore[$i]->ratingValue!!}</td>
                            <td>{{date('Y-m-d', strtotime($newScore[$i]->updated_at))}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
    <article class="box">
        <h3 class="title is-3">ベスト枠 レーティング対象曲</h3>
        <div id="sort_table" class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @for ($i = 0; $i < $statistics->oldBestRatingCount; $i++)
                        <tr>
                            <td>{{$oldScore[$i]->title}}</td>
                            <td>{{substr($oldScore[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{{$oldScore[$i]->level_str}}</td>
                            <td>{{number_format($oldScore[$i]->technical_high_score)}}</td>
                            <td>{!!$oldScore[$i]->ratingValue!!}</td>
                            <td>{{date('Y-m-d', strtotime($oldScore[$i]->updated_at))}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
    <article class="box">
        <h3 class="title is-3">リーセント枠 レーティング対象曲</h3>
        <div id="sort_table" class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Title</th>
                        <th><abbr title="Difficulty">Dif</abbr></th>
                        <th>Lv</th>
                        <th><abbr title="Technical Score">TS</abbr></th>
                        <th>Rate</th>
                        <th>Update</th>
                    </tr>
                </tfoot>
                <tbody class="list">
                    @for ($i = 0; $i < $statistics->recentRatingCount; $i++)
                        <tr>
                            <td>{{$recentScore[$i]->title}}</td>
                            <td>{{substr($recentScore[$i]->difficulty_str, 0, 3)}}</td>
                            <td>{{$recentScore[$i]->level_str}}</td>
                            <td>{{number_format($recentScore[$i]->technical_score)}}</td>
                            <td>{!!$recentScore[$i]->ratingValue!!}</td>
                            <td>{{date('Y-m-d', strtotime($recentScore[$i]->updated_at))}}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </article>
@endsection