<article class="box">
    {{$badge}}
    <table class="table is-striped">
        <tbody>
            <tr>
                <th>プレイヤーネーム</th>
                <td>{{$name}}</td>
            </tr>
            <tr>
                <th>トロフィー</th>
                <td>{{$trophy}}</td>
            </tr>
            <tr>
                <th>レベル</th>
                <td>{{$level}}</td>
            </tr>
            <tr>
                <th>バトルポイント</th>
                <td>{{$battle_point}}</td>
            </tr>
            <tr>
                <th>レーティング</th>
                <td>{{$rating}} (MAX {{$rating_max}})</td>
            </tr>
            <tr>
                <th>マニー</th>
                <td>{{$money}} (Total {{$money_max}})</td>
            </tr>
            <tr>
                <th>トータルプレイ</th>
                <td>{{$total_play}}</td>
            </tr>
            <tr>
                <th>コメント</th>
                <td>{{$comment}}</td>
            </tr>
        </tbody>
    </table>
</article>
