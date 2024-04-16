<p>
    <b>表示モード</b><br>
    <div style="margin-bottom: .3em">
        <a href="/user/{{$id}}/{{$mode}}" class="button{{($archive === 0) ? " is-info" : ""}}">現行譜面のみ</a>
        <a href="/user/{{$id}}/{{$mode}}?archive=1" class="button{{($archive === 1) ? " is-info" : ""}}">現行譜面のみ / スコア0を表示</a><br>
    </div>
    <div style="margin-bottom: .3em">
        <a href="/user/{{$id}}/{{$mode}}?archive=2" class="button{{($archive === 2) ? " is-info" : ""}}">削除譜面のみ</a>
        <a href="/user/{{$id}}/{{$mode}}?archive=3" class="button{{($archive === 3) ? " is-info" : ""}}">削除譜面のみ / スコア0を表示</a><br>
    </div>
    <div>
        <a href="/user/{{$id}}/{{$mode}}?archive=4" class="button{{($archive === 4) ? " is-info" : ""}}">すべて表示（従来モード）</a><br>
    </div>

</p>
