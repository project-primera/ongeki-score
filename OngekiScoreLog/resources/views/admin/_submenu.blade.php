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

@if (isset($active) && $active === 'aggregate')
    <li class="is-active">
@else
    <li>
@endif
<a href="/admin/aggregate">aggregate</a></li>
