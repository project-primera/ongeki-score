<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesUserStatus;
use Tests\TestCase;

/**
 * ユーザー一覧（/user）は、公開ユーザーの最新ステータスを 1 ページ 100 件ずつ表示する。
 * 並び順の指定が無ければ更新の新しい順。最新ステータスは、そのユーザーの user_status のうち id が最大の行。
 * 非公開ユーザーは、誰が見ても一覧に出ない。
 */
class UserListPaginationTest extends TestCase
{
    use CreatesUserStatus;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['env.ongeki-version-date' => '2026/01/01']);
    }

    /** $i 人目ほど更新が新しい（1 分ずつずらす）ステータス */
    private function updatedMinutesLater(int $i): array
    {
        return ['updated_at' => date('Y-m-d H:i:s', strtotime('2026-06-01 00:00:00') + $i * 60)];
    }

    public function test_first_page_shows_100_users_newest_update_first(): void
    {
        $ids = $this->createUsers(250, fn (int $i) => $this->updatedMinutesLater($i));

        $response = $this->get('/user')->assertStatus(200);

        $this->assertSame(array_slice(array_reverse($ids), 0, 100), $this->listedUserIds($response));
    }

    public function test_following_pages_continue_without_overlap(): void
    {
        $ids = $this->createUsers(250, fn (int $i) => $this->updatedMinutesLater($i));
        $newestFirst = array_reverse($ids);

        $this->assertSame(array_slice($newestFirst, 100, 100), $this->listedUserIds($this->get('/user?page=2')));
        $this->assertSame(array_slice($newestFirst, 200, 50), $this->listedUserIds($this->get('/user?page=3')));
    }

    public function test_exactly_100_users_fit_in_one_page(): void
    {
        $this->createUsers(100, fn (int $i) => $this->updatedMinutesLater($i));

        $this->assertCount(100, $this->listedUserIds($this->get('/user')));
        $this->assertCount(0, $this->listedUserIds($this->get('/user?page=2')));
    }

    public function test_the_101st_user_goes_to_the_second_page(): void
    {
        $ids = $this->createUsers(101, fn (int $i) => $this->updatedMinutesLater($i));

        $this->assertCount(100, $this->listedUserIds($this->get('/user')));
        // いちばん古い 1 人だけが 2 ページ目に出る
        $this->assertSame([$ids[0]], $this->listedUserIds($this->get('/user?page=2')));
    }

    public function test_page_size_cannot_be_changed_by_a_parameter(): void
    {
        $this->createUsers(250);

        $this->assertCount(100, $this->listedUserIds($this->get('/user?per_page=500')));
        $this->assertCount(100, $this->listedUserIds($this->get('/user?limit=500')));
    }

    public function test_invalid_page_shows_the_first_page(): void
    {
        $ids = $this->createUsers(150, fn (int $i) => $this->updatedMinutesLater($i));
        $firstPage = array_slice(array_reverse($ids), 0, 100);

        foreach (['page=abc', 'page=0', 'page=-1', 'page=1.5', 'page[]=2', 'page='] as $query) {
            $response = $this->get('/user?'.$query)->assertStatus(200);

            $this->assertSame($firstPage, $this->listedUserIds($response), $query);
        }
    }

    public function test_row_shows_the_status_of_the_user(): void
    {
        $user = $this->createUser([
            'name' => 'オンゲキ',
            'trophy' => '称号',
            'level' => 123,
            'rating' => 15.5,
            'rating_max' => 16,
            'battle_point' => 12345,
            'updated_at' => '2026-06-07 08:09:10',
        ]);

        $response = $this->get('/user')->assertStatus(200);

        $this->assertSame(
            [[(string) $user->id, 'オンゲキ', '称号', '123', '15.500', '16.000', '12345', '2026-06-07']],
            $this->listedRows($response)
        );
        $response->assertSee('<a href="/user/'.$user->id.'">オンゲキ</a>', false);
    }

    public function test_name_and_trophy_are_escaped(): void
    {
        $this->createUser(['name' => '<b>x</b>', 'trophy' => '<i>y</i>']);

        $html = $this->get('/user')->assertStatus(200)->getContent();

        $this->assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;y&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>x</b>', $html);
    }

    public function test_only_the_latest_status_of_each_user_is_listed(): void
    {
        $user = $this->createUser(['rating' => 10, 'updated_at' => '2026-06-01 00:00:00']);
        $this->addStatus($user, ['rating' => 11, 'updated_at' => '2026-06-02 00:00:00']);
        $this->addStatus($user, ['rating' => 12, 'updated_at' => '2026-06-03 00:00:00']);

        $rows = $this->listedRows($this->get('/user'));

        $this->assertCount(1, $rows);
        $this->assertSame('12.000', $rows[0][4]);
        $this->assertSame('2026-06-03', $rows[0][7]);
    }

    public function test_latest_status_is_the_row_with_the_largest_id(): void
    {
        // 同じ時刻の行が 2 つあっても 1 行だけ出て、後から入った方（id が大きい方）になる
        $user = $this->createUser(['rating' => 10, 'updated_at' => '2026-06-01 00:00:00']);
        $this->addStatus($user, ['rating' => 11, 'updated_at' => '2026-06-01 00:00:00']);

        $rows = $this->listedRows($this->get('/user'));

        $this->assertCount(1, $rows);
        $this->assertSame('11.000', $rows[0][4]);

        // 時刻が前後していても、後から入った行を最新として扱う
        $other = $this->createUser(['rating' => 5, 'updated_at' => '2026-06-05 00:00:00']);
        $this->addStatus($other, ['rating' => 6, 'updated_at' => '2026-06-04 00:00:00']);

        $rows = array_column($this->listedRows($this->get('/user')), null, 0);
        $this->assertSame('6.000', $rows[$other->id][4]);
    }

    public function test_private_users_are_hidden_from_everyone(): void
    {
        $public = $this->createUser(['name' => 'visible']);
        $private = $this->createUser(['name' => 'hidden'], 1);
        $admin = $this->createUser(null, 0, 7);

        $this->assertSame([$public->id], $this->listedUserIds($this->get('/user')));
        $this->assertSame([$public->id], $this->listedUserIds($this->actingAs($private)->get('/user')));
        $this->assertSame([$public->id], $this->listedUserIds($this->actingAs($admin)->get('/user')));
    }

    public function test_users_without_status_are_not_listed(): void
    {
        $withStatus = $this->createUser();
        $this->createUser(null);

        $this->assertSame([$withStatus->id], $this->listedUserIds($this->get('/user')));
    }

    public function test_users_with_the_same_update_time_are_ordered_by_id_across_pages(): void
    {
        $ids = $this->createUsers(150);

        $this->assertSame(array_slice($ids, 0, 100), $this->listedUserIds($this->get('/user')));
        $this->assertSame(array_slice($ids, 100), $this->listedUserIds($this->get('/user?page=2')));
    }

    public function test_page_has_no_client_side_sort_markup(): void
    {
        $this->createUser();

        $html = $this->get('/user')->assertStatus(200)->getContent();

        $this->assertStringNotContainsString('sortAllUserTable', $html);
        $this->assertStringNotContainsString('data-sort', $html);
        $this->assertStringNotContainsString('sort-key', $html);
        $this->assertStringNotContainsString('id="sort_table"', $html);
    }

    public function test_alluser_redirects_to_user(): void
    {
        $this->get('/alluser')->assertStatus(301)->assertRedirect('/user');
    }
}
