@extends('layouts.app')

@section('title', $status[0]->name . " / [" . ucfirst($difficulty) . "] " . $score[0]->title)
@section('hero_subtitle', $status[0]->name . " - 楽曲詳細")
@section('hero_title', "[" . ucfirst($difficulty) . "] " . $score[0]->title)
@section('additional_footer')
    {!!$highcharts!!}
    {!!$highcharts_sp!!}
    <script type="text/javascript" src="{{ mix('/js/changeGraphSize.js') }}"></script>
@endsection
@if(isset($sidemark) && !is_null($sidemark))
    @section($sidemark, "is-active")
@endif

@section('submenu')
    <li><a href="/user/{{$id}}">簡易</a></li>
    <li><a href="/user/{{$id}}/details">詳細</a></li>
    <li><a href="/user/{{$id}}/battle">Battle</a></li>
    <li><a href="/user/{{$id}}/technical">Technical</a></li>
    <li><a href="/user/{{$id}}/trophy">称号</a></li>
    <li><a href="/user/{{$id}}/rating">Rating</a></li>
    <li><a href="/user/{{$id}}/progress">更新差分</a></li>
@endsection

@section('content')
    <article class="box">
        <div id="graph"></div>
        <div id="sp-graph"></div>
        <button class="button change-graph-size">軸表示切り替え</button>
    </article>
    <article class="box">
        <div class="table_wrap scalable">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="2">Technical Score</th>
                        <th colspan="2">Battle Score</th>
                        <th colspan="2">Over Damage</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="2">Technical Score</th>
                        <th colspan="2">Battle Score</th>
                        <th colspan="2">Over Damage</th>
                        <th>Date</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($score as $item)
                        <tr>
                            <td>{{number_format($item->technical_high_score)}}</td>
                            @if ($loop->first || $item->differenceTechnical == 0)
                                <td></td>
                            @else
                                <td>+{{number_format($item->differenceTechnical)}}</td>
                            @endif

                            <td>{{number_format($item->battle_high_score)}}</td>
                            @if ($loop->first || $item->differenceBattle == 0)
                                <td></td>
                            @else
                                <td>+{{number_format($item->differenceBattle)}}</td>
                            @endif

                            <td>{{$item->over_damage_high_score}}%</td>
                            @if ($loop->first || $item->differenceDamage == 0)
                                <td></td>
                            @else
                                <td>+{{$item->differenceDamage}}%</td>
                            @endif

                            <td>{{date("Y/m/d H:i", strtotime($item->updated_at))}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </article>
@endsection