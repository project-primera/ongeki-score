{{--
    ページ送り（Bulma の pagination）。$paginator->links('layouts.components.pagination') で使う。
    ページ違いの URL を検索エンジンにたどらせないよう、リンクには rel="nofollow" を付ける。
--}}
@if ($paginator->hasPages())
    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        @if ($paginator->onFirstPage())
            <a class="pagination-previous" disabled>前へ</a>
        @else
            {{-- 最終ページより先を開いている場合は、最終ページへ戻す --}}
            <a class="pagination-previous" href="{{ $paginator->url(min($paginator->currentPage() - 1, $paginator->lastPage())) }}" rel="nofollow">前へ</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="pagination-next" href="{{ $paginator->nextPageUrl() }}" rel="nofollow">次へ</a>
        @else
            <a class="pagination-next" disabled>次へ</a>
        @endif

        <ul class="pagination-list">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="pagination-ellipsis">&hellip;</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><a class="pagination-link is-current" aria-label="{{ $page }} ページ目" aria-current="page">{{ $page }}</a></li>
                        @else
                            <li><a class="pagination-link" href="{{ $url }}" rel="nofollow" aria-label="{{ $page }} ページ目へ">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
    </nav>
@endif
