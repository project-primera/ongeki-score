<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesUserStatus;
use Tests\TestCase;

/**
 * ユーザー一覧（/user）は、並び替え・ページ・検索の組み合わせで URL が大量にできる。
 * クローラーがそれらを片端から読みに来ないよう、一覧の中を移動するリンクには rel="nofollow" を付け、
 * /user そのもの以外（page / sort / order / q のどれかが付いた URL）は noindex にする。
 * 各ユーザーのページへのリンクは、これまでどおりたどれる。
 */
class UserListRobotsTest extends TestCase
{
    use CreatesUserStatus;
    use RefreshDatabase;

    private const NOINDEX = '<meta name="robots" content="noindex">';

    protected function setUp(): void
    {
        parent::setUp();

        config(['env.ongeki-version-date' => '2026/01/01']);
    }

    public function test_sort_and_pager_links_are_nofollow(): void
    {
        $this->createUsers(250);

        foreach (['/user', '/user?page=2', '/user?sort=rating&order=desc', '/user?q=player'] as $url) {
            $response = $this->get($url)->assertStatus(200);
            $links = array_values(array_filter(
                $this->anchors($response),
                fn (array $anchor): bool => str_starts_with($anchor['href'] ?? '', '/user?')
            ));

            // 列見出し 8 列 × 上下 + ページ送り × 上下
            $this->assertGreaterThan(16, count($links), $url);
            foreach ($links as $link) {
                $this->assertSame('nofollow', $link['rel'] ?? null, $url.' → '.$link['href']);
            }
        }
    }

    public function test_clear_search_link_is_nofollow(): void
    {
        $this->createUser(['name' => 'alice']);

        foreach (['/user?q=ali', '/user?q=ali&sort=bp&order=desc'] as $url) {
            $clear = array_column($this->anchors($this->get($url)->assertStatus(200)), null, 'text')['検索を解除'];

            $this->assertSame('nofollow', $clear['rel'] ?? null, $url);
        }
    }

    public function test_links_to_user_pages_are_not_nofollow(): void
    {
        $user = $this->createUser();

        $response = $this->get('/user')->assertStatus(200);
        $links = array_values(array_filter(
            $this->anchors($response),
            fn (array $anchor): bool => ($anchor['href'] ?? '') === '/user/'.$user->id
        ));

        $this->assertNotEmpty($links);
        foreach ($links as $link) {
            $this->assertArrayNotHasKey('rel', $link);
        }
    }

    public function test_urls_with_list_parameters_are_noindex(): void
    {
        $this->createUsers(250);

        $queries = [
            'page=2',
            'page=1',
            'page=9999',
            'sort=rating',
            'sort=rating&order=desc',
            'sort=foo',
            'order=desc',
            'q=player',
            'q=',
            'q[]=a',
            'sort=update&order=desc',
        ];
        foreach ($queries as $query) {
            $this->get('/user?'.$query)->assertStatus(200)->assertSee(self::NOINDEX, false);
        }
    }

    public function test_the_plain_list_is_indexable(): void
    {
        $this->createUsers(250);

        // 一覧と関係のないパラメータだけなら、/user と同じ内容なので noindex にしない
        foreach (['/user', '/user?', '/user?utm_source=x'] as $url) {
            $this->get($url)->assertStatus(200)->assertDontSee('name="robots"', false);
        }
    }

    public function test_an_empty_list_is_indexable_only_without_parameters(): void
    {
        $this->get('/user')->assertStatus(200)->assertDontSee('name="robots"', false);
        $this->get('/user?q=zzzz')->assertStatus(200)->assertSee(self::NOINDEX, false);
    }
}
