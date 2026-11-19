<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesUserStatus;
use Tests\TestCase;

/**
 * ユーザー一覧（/user）は、列見出しのリンクで全ユーザーを対象に並び替えられる（?sort=…&order=…）。
 * Rating / Max の並び替えでは、向きに関係なく現行バージョンで更新したユーザーを先に並べ、
 * それより前の更新のユーザー（Rating を括弧付きで出す）は後ろにまとめる。
 * 不正な指定はエラーにせず、既定の並び（Update の降順）に寄せる。
 */
class UserListSortTest extends TestCase
{
    use CreatesUserStatus;
    use RefreshDatabase;

    private const CURRENT = '2026-06-01 00:00:00';
    private const OLD = '2025-06-01 00:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        config(['env.ongeki-version-date' => '2026/01/01']);
    }

    private function listedIds(string $query): array
    {
        return $this->listedUserIds($this->get('/user?'.$query)->assertStatus(200));
    }

    /**
     * 列見出し（thead または tfoot）を、表示名 => ['href' => リンク先, 'class' => th の class, 'rel' => rel] にして返す。
     */
    private function headings(TestResponse $response, string $section = 'thead'): array
    {
        if (!preg_match('#<'.$section.'[^>]*>(.*?)</'.$section.'>#s', $response->getContent(), $match)) {
            return [];
        }
        preg_match_all('#<th class="([^"]*)">(.*?)</th>#s', $match[1], $cells, PREG_SET_ORDER);

        $headings = [];
        foreach ($cells as $cell) {
            $anchor = $this->anchors($response, $cell[2])[0];
            $headings[$anchor['text']] = ['href' => $anchor['href'], 'class' => $cell[1], 'rel' => $anchor['rel'] ?? null];
        }

        return $headings;
    }

    public function test_sort_by_id(): void
    {
        $a = $this->createUser(['updated_at' => '2026-06-02 00:00:00']);
        $b = $this->createUser(['updated_at' => '2026-06-03 00:00:00']);
        $c = $this->createUser(['updated_at' => '2026-06-01 00:00:00']);

        $this->assertSame([$a->id, $b->id, $c->id], $this->listedIds('sort=id&order=asc'));
        $this->assertSame([$c->id, $b->id, $a->id], $this->listedIds('sort=id&order=desc'));
    }

    public function test_level_and_battle_point_are_sorted_as_numbers(): void
    {
        $nine = $this->createUser(['level' => 9, 'battle_point' => 100]);
        $hundred = $this->createUser(['level' => 100, 'battle_point' => 9]);
        $ten = $this->createUser(['level' => 10, 'battle_point' => 10]);

        $this->assertSame([$nine->id, $ten->id, $hundred->id], $this->listedIds('sort=lv&order=asc'));
        $this->assertSame([$hundred->id, $ten->id, $nine->id], $this->listedIds('sort=lv&order=desc'));
        $this->assertSame([$hundred->id, $ten->id, $nine->id], $this->listedIds('sort=bp&order=asc'));
        $this->assertSame([$nine->id, $ten->id, $hundred->id], $this->listedIds('sort=bp&order=desc'));
    }

    public function test_name_and_trophy_are_sorted_as_text(): void
    {
        $bob = $this->createUser(['name' => 'bob', 'trophy' => 'gold']);
        $alice = $this->createUser(['name' => 'alice', 'trophy' => 'silver']);
        $carol = $this->createUser(['name' => 'carol', 'trophy' => 'bronze']);

        $this->assertSame([$alice->id, $bob->id, $carol->id], $this->listedIds('sort=name&order=asc'));
        $this->assertSame([$carol->id, $bob->id, $alice->id], $this->listedIds('sort=name&order=desc'));
        $this->assertSame([$carol->id, $bob->id, $alice->id], $this->listedIds('sort=trophy&order=asc'));
        $this->assertSame([$alice->id, $bob->id, $carol->id], $this->listedIds('sort=trophy&order=desc'));
    }

    public function test_update_is_sorted_down_to_the_second(): void
    {
        $noon = $this->createUser(['updated_at' => '2026-06-01 12:00:00']);
        $morning = $this->createUser(['updated_at' => '2026-06-01 08:00:00']);
        $nextDay = $this->createUser(['updated_at' => '2026-06-02 00:00:00']);
        $oneSecondLater = $this->createUser(['updated_at' => '2026-06-01 12:00:01']);

        $this->assertSame([$morning->id, $noon->id, $oneSecondLater->id, $nextDay->id], $this->listedIds('sort=update&order=asc'));
        $this->assertSame([$nextDay->id, $oneSecondLater->id, $noon->id, $morning->id], $this->listedIds('sort=update&order=desc'));
    }

    public function test_sort_covers_all_users_not_only_the_current_page(): void
    {
        // 更新の新しい順と BP の高い順が逆になるようにする
        $ids = $this->createUsers(150, fn (int $i) => [
            'battle_point' => 1000 - $i,
            'updated_at' => date('Y-m-d H:i:s', strtotime('2026-06-01 00:00:00') + $i * 60),
        ]);

        $this->assertSame(array_slice($ids, 0, 100), $this->listedIds('sort=bp&order=desc'));
        $this->assertSame(array_slice($ids, 100), $this->listedIds('sort=bp&order=desc&page=2'));
    }

    public function test_equal_values_are_ordered_by_id_across_pages(): void
    {
        $ids = $this->createUsers(150, fn (int $i) => ['level' => 50]);

        foreach (['asc', 'desc'] as $order) {
            $this->assertSame(array_slice($ids, 0, 100), $this->listedIds('sort=lv&order='.$order), $order);
            $this->assertSame(array_slice($ids, 100), $this->listedIds('sort=lv&order='.$order.'&page=2'), $order);
        }
    }

    public function test_rating_puts_current_version_users_first_in_both_orders(): void
    {
        $p = $this->createUser(['rating' => 15, 'updated_at' => self::CURRENT]);
        $r = $this->createUser(['rating' => 18, 'updated_at' => self::OLD]);
        $q = $this->createUser(['rating' => 17, 'updated_at' => self::CURRENT]);
        $s = $this->createUser(['rating' => 16, 'updated_at' => self::OLD]);

        $this->assertSame([$q->id, $p->id, $r->id, $s->id], $this->listedIds('sort=rating&order=desc'));
        $this->assertSame([$p->id, $q->id, $s->id, $r->id], $this->listedIds('sort=rating&order=asc'));
    }

    public function test_max_puts_current_version_users_first_in_both_orders(): void
    {
        // Rating の順と Max の順が違うデータにして、rating_max で並んでいることを確かめる
        $p = $this->createUser(['rating' => 15, 'rating_max' => 17.5, 'updated_at' => self::CURRENT]);
        $r = $this->createUser(['rating' => 18, 'rating_max' => 18.2, 'updated_at' => self::OLD]);
        $q = $this->createUser(['rating' => 17, 'rating_max' => 17.1, 'updated_at' => self::CURRENT]);
        $s = $this->createUser(['rating' => 16, 'rating_max' => 19, 'updated_at' => self::OLD]);

        $this->assertSame([$p->id, $q->id, $s->id, $r->id], $this->listedIds('sort=max&order=desc'));
        $this->assertSame([$q->id, $p->id, $r->id, $s->id], $this->listedIds('sort=max&order=asc'));
    }

    public function test_other_columns_do_not_separate_versions(): void
    {
        $current = $this->createUser(['battle_point' => 100, 'level' => 1, 'updated_at' => self::CURRENT]);
        $old = $this->createUser(['battle_point' => 200, 'level' => 2, 'updated_at' => self::OLD]);

        $this->assertSame([$old->id, $current->id], $this->listedIds('sort=bp&order=desc'));
        $this->assertSame([$old->id, $current->id], $this->listedIds('sort=lv&order=desc'));
        $this->assertSame([$old->id, $current->id], $this->listedIds('sort=id&order=desc'));
    }

    public function test_rating_before_the_current_version_is_parenthesized(): void
    {
        $current = $this->createUser(['rating' => 15.123, 'rating_max' => 16, 'updated_at' => self::CURRENT]);
        $old = $this->createUser(['rating' => 18.5, 'rating_max' => 19.001, 'updated_at' => self::OLD]);

        $rows = array_column($this->listedRows($this->get('/user')), null, 0);

        $this->assertSame(['15.123', '16.000'], array_slice($rows[$current->id], 4, 2));
        $this->assertSame(['(18.500)', '(19.001)'], array_slice($rows[$old->id], 4, 2));
    }

    public function test_update_exactly_at_the_version_start_counts_as_current(): void
    {
        $justBefore = $this->createUser(['rating' => 18, 'updated_at' => '2025-12-31 23:59:59']);
        $exactly = $this->createUser(['rating' => 10, 'updated_at' => '2026-01-01 00:00:00']);

        $response = $this->get('/user?sort=rating&order=desc')->assertStatus(200);
        $rows = array_column($this->listedRows($response), null, 0);

        $this->assertSame([$exactly->id, $justBefore->id], $this->listedUserIds($response));
        $this->assertSame('10.000', $rows[$exactly->id][4]);
        $this->assertSame('(18.000)', $rows[$justBefore->id][4]);
    }

    public function test_rating_sort_works_when_everyone_is_on_the_same_side(): void
    {
        $low = $this->createUser(['rating' => 10, 'updated_at' => self::OLD]);
        $high = $this->createUser(['rating' => 12, 'updated_at' => self::OLD]);

        $this->assertSame([$high->id, $low->id], $this->listedIds('sort=rating&order=desc'));
        $this->assertSame([$low->id, $high->id], $this->listedIds('sort=rating&order=asc'));
    }

    public function test_without_a_version_date_everyone_counts_as_current(): void
    {
        config(['env.ongeki-version-date' => null]);
        $recent = $this->createUser(['rating' => 15, 'updated_at' => self::CURRENT]);
        $longAgo = $this->createUser(['rating' => 18, 'updated_at' => '2019-01-01 00:00:00']);

        $response = $this->get('/user?sort=rating&order=desc')->assertStatus(200);

        $this->assertSame([$longAgo->id, $recent->id], $this->listedUserIds($response));
        $this->assertSame('18.000', array_column($this->listedRows($response), null, 0)[$longAgo->id][4]);
    }

    public function test_headings_link_to_the_first_order_of_each_column(): void
    {
        $this->createUser();

        $headings = $this->headings($this->get('/user')->assertStatus(200));

        $this->assertSame(
            [
                'ID' => '/user?sort=id&order=asc',
                'Name' => '/user?sort=name&order=asc',
                'Trophy' => '/user?sort=trophy&order=asc',
                'Lv' => '/user?sort=lv&order=desc',
                'Rating' => '/user?sort=rating&order=desc',
                '(Max)' => '/user?sort=max&order=desc',
                'BP' => '/user?sort=bp&order=desc',
                // 既定の並びが Update の降順なので、Update の見出しは逆向きになる
                'Update' => '/user?sort=update&order=asc',
            ],
            array_map(fn (array $heading): string => $heading['href'], $headings)
        );
    }

    public function test_heading_of_the_current_column_shows_the_order_and_reverses_it(): void
    {
        $this->createUser();

        $default = $this->headings($this->get('/user'));
        $this->assertSame('sort desc', $default['Update']['class']);
        $this->assertSame('sort', $default['Rating']['class']);

        $descending = $this->headings($this->get('/user?sort=rating&order=desc'));
        $this->assertSame('sort desc', $descending['Rating']['class']);
        $this->assertSame('/user?sort=rating&order=asc', $descending['Rating']['href']);
        $this->assertSame('sort', $descending['Update']['class']);
        $this->assertSame('/user?sort=update&order=desc', $descending['Update']['href']);

        $ascending = $this->headings($this->get('/user?sort=rating&order=asc'));
        $this->assertSame('sort asc', $ascending['Rating']['class']);
        $this->assertSame('/user?sort=rating&order=desc', $ascending['Rating']['href']);
    }

    public function test_headings_are_the_same_above_and_below_the_table(): void
    {
        $this->createUser();

        $response = $this->get('/user?sort=bp&order=asc')->assertStatus(200);

        $this->assertCount(8, $this->headings($response, 'thead'));
        $this->assertSame($this->headings($response, 'thead'), $this->headings($response, 'tfoot'));
    }

    public function test_headings_go_back_to_the_first_page(): void
    {
        $this->createUsers(250);

        $headings = $this->headings($this->get('/user?page=3')->assertStatus(200));

        $this->assertCount(8, $headings);
        foreach ($headings as $label => $heading) {
            $this->assertStringNotContainsString('page', $heading['href'], $label);
        }
    }

    public function test_pager_keeps_the_chosen_sort(): void
    {
        $this->createUsers(250);

        $hrefs = array_column($this->pagerAnchors($this->get('/user?sort=bp&order=asc&page=2')), 'href', 'text');

        $this->assertSame('/user?sort=bp&order=asc&page=1', $hrefs['前へ']);
        $this->assertSame('/user?sort=bp&order=asc&page=3', $hrefs['次へ']);
    }

    public function test_pager_carries_no_sort_until_a_sort_is_chosen(): void
    {
        $this->createUsers(250);

        $hrefs = array_column($this->pagerAnchors($this->get('/user?page=2')), 'href', 'text');

        $this->assertSame('/user?page=1', $hrefs['前へ']);
        $this->assertSame('/user?page=3', $hrefs['次へ']);
    }

    public function test_unknown_sort_shows_the_default_order(): void
    {
        $older = $this->createUser(['updated_at' => '2026-06-01 00:00:00']);
        $newer = $this->createUser(['updated_at' => '2026-06-02 00:00:00']);

        foreach (['sort=foo&order=asc', 'sort=user_id&order=asc', 'sort[]=id&order=asc', 'order=asc', 'sort=&order=asc'] as $query) {
            $this->assertSame([$newer->id, $older->id], $this->listedIds($query), $query);
        }
    }

    public function test_invalid_order_uses_the_first_order_of_the_column(): void
    {
        $low = $this->createUser(['name' => 'bob', 'rating' => 10, 'updated_at' => '2026-06-02 00:00:00']);
        $high = $this->createUser(['name' => 'alice', 'rating' => 12, 'updated_at' => '2026-06-01 00:00:00']);

        foreach (['sort=rating', 'sort=rating&order=foo', 'sort=rating&order[]=asc'] as $query) {
            $this->assertSame([$high->id, $low->id], $this->listedIds($query), $query);
        }
        foreach (['sort=name', 'sort=name&order=DESC'] as $query) {
            $this->assertSame([$high->id, $low->id], $this->listedIds($query), $query);
        }
    }

    public function test_sql_fragments_in_sort_and_order_are_not_executed(): void
    {
        $older = $this->createUser(['updated_at' => '2026-06-01 00:00:00']);
        $newer = $this->createUser(['updated_at' => '2026-06-02 00:00:00']);

        $ids = $this->listedIds('sort='.urlencode('rating;DROP TABLE users').'&order='.urlencode('desc,1'));

        $this->assertSame([$newer->id, $older->id], $ids);
        $this->assertDatabaseCount('users', 2);
    }
}
