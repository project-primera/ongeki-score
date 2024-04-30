@if (isset($active) && $active === 'index')
    <li class="is-active">
@else
    <li>
@endif
<a href="/admin/">Top</a></li>

@if (isset($active) && $active === 'config')
    <li class="is-active">
@else
    <li>
@endif
<a href="/admin/config">config</a></li>

@if (isset($active) && $active === 'battle')
    <li class="is-active">
@else
    <li>
@endif
<a href="/admin/battle">battle</a></li>

@if (isset($active) && $active === 'overdamage')
    <li class="is-active">
@else
    <li>
@endif
<a href="/admin/overdamage">overdamage</a></li>
