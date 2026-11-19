<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesUserStatus;
use Tests\TestCase;

/**
 * ユーザー一覧（/user）は、名前の一部で検索できる（?q=…）。
 * 探すのは一覧に出ている名前（最新ステータスの名前）だけで、非公開ユーザーは検索しても出ない。
 * 検索語の % や _ はその文字そのものとして探し、100 文字を超える分は捨てる。
 * 検索語は並び替えやページ送りのリンクに引き継がれる。
 */
class UserListSearchTest extends TestCase
{
    use CreatesUserStatus;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['env.ongeki-version-date' => '2026/01/01']);
    }

    private function listedNames(string $query): array
    {
        return array_column($this->listedRows($this->get('/user?'.$query)->assertStatus(200)), 1);
    }

    /** 検索フォームの中身（form 要素の内側の HTML） */
    private function searchForm(TestResponse $response): string
    {
        $this->assertSame(1, preg_match('#<form method="get" action="/user"[^>]*>(.*?)</form>#s', $response->getContent(), $match));

        return $match[1];
    }

    private function clearLink(TestResponse $response): ?array
    {
        foreach ($this->anchors($response) as $anchor) {
            if ($anchor['text'] === '検索を解除') {
                return $anchor;
            }
        }

        return null;
    }

    public function test_search_matches_any_part_of_the_name(): void
    {
        $this->createUser(['name' => 'alice']);
        $this->createUser(['name' => 'alicia']);
        $this->createUser(['name' => 'bob']);

        $this->assertSame(['alice', 'alicia'], $this->listedNames('q=ali'));
        $this->assertSame(['alice', 'alicia'], $this->listedNames('q=ic'));
        $this->assertSame(['alice'], $this->listedNames('q=ce'));
        $this->assertSame(['bob'], $this->listedNames('q=ob'));
        $this->assertSame(['bob'], $this->listedNames('q=bob'));
    }

    public function test_search_matches_multibyte_names(): void
    {
        $this->createUser(['name' => 'オンゲキ']);
        $this->createUser(['name' => 'あかね']);

        $this->assertSame(['オンゲキ'], $this->listedNames('q='.urlencode('ンゲ')));
        $this->assertSame(['あかね'], $this->listedNames('q='.urlencode('かね')));
    }

    public function test_search_looks_only_at_the_latest_name(): void
    {
        $user = $this->createUser(['name' => 'oldname']);
        $this->addStatus($user, ['name' => 'newname']);

        $this->assertSame([], $this->listedNames('q=oldname'));
        $this->assertSame(['newname'], $this->listedNames('q=newname'));
    }

    public function test_private_users_are_not_found(): void
    {
        $this->createUser(['name' => 'alice']);
        $this->createUser(['name' => 'alison'], 1);

        $this->assertSame(['alice'], $this->listedNames('q=ali'));

        $response = $this->get('/user?q=alison')->assertStatus(200);
        $response->assertSee('該当するユーザーがいません');
        $response->assertDontSee('検索結果');
    }

    public function test_wildcard_characters_are_matched_literally(): void
    {
        $this->createUser(['name' => 'a%b']);
        $this->createUser(['name' => 'axb']);
        $this->createUser(['name' => 'a_b']);
        $this->createUser(['name' => 'a!b']);
        $this->createUser(['name' => 'a\\b']);

        $this->assertSame(['a%b'], $this->listedNames('q='.urlencode('%')));
        $this->assertSame(['a\\b'], $this->listedNames('q='.urlencode('\\')));
        $this->assertSame(['a\\b'], $this->listedNames('q='.urlencode('a\\b')));
        $this->assertSame(['a_b'], $this->listedNames('q=_'));
        $this->assertSame(['a!b'], $this->listedNames('q='.urlencode('!')));
        $this->assertSame(['a%b'], $this->listedNames('q='.urlencode('a%b')));
        $this->assertSame(['a_b'], $this->listedNames('q=a_b'));
        $this->assertSame(['axb'], $this->listedNames('q=x'));
    }

    public function test_no_match_shows_a_message(): void
    {
        $this->createUser(['name' => 'alice']);

        $response = $this->get('/user?q=zzzz')->assertStatus(200);

        $response->assertSee('該当するユーザーがいません');
        $response->assertDontSee('<table', false);
        $this->assertSame([], $this->listedUserIds($response));
    }

    public function test_blank_keyword_means_no_search(): void
    {
        $this->createUser(['name' => 'alice']);
        $this->createUser(['name' => 'bob']);

        foreach (['q=', 'q=%20%20', 'q=%E3%80%80'] as $query) {
            $response = $this->get('/user?'.$query)->assertStatus(200);

            $this->assertCount(2, $this->listedUserIds($response), $query);
            $response->assertDontSee('検索結果');
            $this->assertNull($this->clearLink($response), $query);
        }
    }

    public function test_keyword_longer_than_100_characters_is_cut(): void
    {
        $name = str_repeat('a', 100);
        $this->createUser(['name' => $name]);
        $this->createUser(['name' => 'bob']);

        $response = $this->get('/user?q='.$name.'b')->assertStatus(200);

        $this->assertSame([$name], array_column($this->listedRows($response), 1));
        $this->assertStringContainsString('value="'.$name.'"', $this->searchForm($response));
    }

    public function test_keyword_is_escaped_in_the_page(): void
    {
        $this->createUser(['name' => 'alice']);

        $html = $this->get('/user?q='.urlencode('"><script>alert(1)</script>'))->assertStatus(200)->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_keyword_that_is_not_a_plain_string_means_no_search(): void
    {
        $this->createUser(['name' => 'alice']);
        $this->createUser(['name' => 'bob']);

        // 配列で渡された値と、UTF-8 として壊れた値
        foreach (['q[]=a', 'q[a]=b', 'q=%FF', 'q=ab%E3%81'] as $query) {
            $response = $this->get('/user?'.$query)->assertStatus(200);

            $this->assertCount(2, $this->listedUserIds($response), $query);
        }
    }

    public function test_search_can_be_combined_with_sort(): void
    {
        $this->createUser(['name' => 'ali-low', 'rating' => 10]);
        $this->createUser(['name' => 'bob', 'rating' => 17]);
        $this->createUser(['name' => 'ali-high', 'rating' => 15]);

        $this->assertSame(['ali-high', 'ali-low'], $this->listedNames('q=ali&sort=rating&order=desc'));
        $this->assertSame(['ali-low', 'ali-high'], $this->listedNames('q=ali&sort=rating&order=asc'));
    }

    public function test_headings_and_pager_keep_the_keyword(): void
    {
        $this->createUsers(150, fn (int $i) => ['name' => 'ali'.$i]);
        $this->createUsers(50, fn (int $i) => ['name' => 'bob'.$i]);

        $response = $this->get('/user?q=ali')->assertStatus(200);
        $hrefs = array_column($this->anchors($response), 'href', 'text');

        $this->assertSame('/user?sort=rating&order=desc&q=ali', $hrefs['Rating']);
        $this->assertSame('/user?sort=update&order=asc&q=ali', $hrefs['Update']);
        $this->assertSame('/user?q=ali&page=2', array_column($this->pagerAnchors($response), 'href', 'text')['次へ']);

        // リンク先でも検索が効いたままになっている
        $second = $this->get('/user?q=ali&page=2')->assertStatus(200);
        $this->assertCount(50, $this->listedUserIds($second));
        $second->assertSee('検索結果 150 人中 101〜150 人目');
    }

    public function test_links_encode_the_keyword(): void
    {
        $this->createUser(['name' => 'a b&c']);
        $this->createUser(['name' => 'other']);

        $response = $this->get('/user?q='.urlencode('a b&c'))->assertStatus(200);
        $href = array_column($this->anchors($response), 'href', 'text')['Name'];

        $this->assertSame(['a b&c'], array_column($this->listedRows($this->get($href)->assertStatus(200)), 1));
    }

    public function test_search_form_keeps_the_chosen_sort_but_not_the_page(): void
    {
        $this->createUsers(250);

        $form = $this->searchForm($this->get('/user?sort=rating&order=asc&page=2')->assertStatus(200));

        $this->assertStringContainsString('<input type="hidden" name="sort" value="rating">', $form);
        $this->assertStringContainsString('<input type="hidden" name="order" value="asc">', $form);
        $this->assertStringNotContainsString('name="page"', $form);
    }

    public function test_search_form_sends_no_sort_until_a_sort_is_chosen(): void
    {
        $this->createUsers(250);

        foreach (['/user', '/user?page=2', '/user?sort=foo'] as $url) {
            $form = $this->searchForm($this->get($url)->assertStatus(200));

            $this->assertStringNotContainsString('type="hidden"', $form, $url);
            $this->assertStringContainsString('name="q"', $form, $url);
        }
    }

    public function test_search_form_is_shown_even_when_nothing_matches(): void
    {
        $form = $this->searchForm($this->get('/user?q=zzzz')->assertStatus(200));

        $this->assertStringContainsString('value="zzzz"', $form);
        $this->assertStringContainsString('maxlength="100"', $form);
    }

    public function test_search_box_keeps_the_keyword_and_offers_a_clear_link(): void
    {
        $this->createUser(['name' => 'alice']);

        $searching = $this->get('/user?q=ali')->assertStatus(200);
        $this->assertStringContainsString('value="ali"', $this->searchForm($searching));
        $this->assertSame('/user', $this->clearLink($searching)['href']);

        $sorted = $this->get('/user?q=ali&sort=bp&order=desc')->assertStatus(200);
        $this->assertSame('/user?sort=bp&order=desc', $this->clearLink($sorted)['href']);

        $this->assertNull($this->clearLink($this->get('/user')->assertStatus(200)));
    }

    public function test_count_is_labelled_as_a_search_result(): void
    {
        $this->createUser(['name' => 'alice']);
        $this->createUser(['name' => 'alicia']);
        $this->createUser(['name' => 'bob']);

        $this->get('/user?q=ali')->assertSee('検索結果 2 人中 1〜2 人目');
        $this->get('/user')->assertSee('3 人中 1〜3 人目')->assertDontSee('検索結果');

        $beyond = $this->get('/user?q=ali&page=5')->assertStatus(200);
        $beyond->assertSee('検索結果 2 人');
        $beyond->assertSee('該当するユーザーがいません');
    }
}
