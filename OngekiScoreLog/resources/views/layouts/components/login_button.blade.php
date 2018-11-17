@if (is_null(\Auth::user()))
    <a href="/register"><i class="fas fa-user"></i>&nbsp;新規登録&nbsp;</a>
    <a href="/login">&nbsp;ログイン&nbsp;</a>
@else
    <a href="/logout"><i class="fas fa-user"></i>&nbsp;ログアウト&nbsp;</a>
@endif