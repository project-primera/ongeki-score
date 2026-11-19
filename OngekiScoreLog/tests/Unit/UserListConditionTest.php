<?php

namespace Tests\Unit;

use App\OngekiScoreLog\UserListCondition;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * ユーザー一覧（/user）の URL クエリ sort / order / q の読み取りと、リンク用クエリの組み立て。
 * 不正な指定はエラーにせず既定値（Update の降順・検索なし）に寄せる。
 * sort / order は、並び替え操作をした状態（URL に有効な sort がある）でだけリンクに引き継ぐ。
 */
class UserListConditionTest extends TestCase
{
    private function condition(array $query = []): UserListCondition
    {
        return UserListCondition::fromRequest(Request::create('/user', 'GET', $query));
    }

    public function test_defaults_to_update_descending_without_keyword(): void
    {
        $condition = $this->condition();

        $this->assertSame('update', $condition->sort());
        $this->assertSame('desc', $condition->order());
        $this->assertSame('updated_at', $condition->column());
        $this->assertNull($condition->keyword());
        $this->assertFalse($condition->isSorted());
    }

    public static function sortProvider(): array
    {
        return [
            'id' => ['id', 'user_id', 'asc'],
            'name' => ['name', 'name', 'asc'],
            'trophy' => ['trophy', 'trophy', 'asc'],
            'lv' => ['lv', 'level', 'desc'],
            'rating' => ['rating', 'rating', 'desc'],
            'max' => ['max', 'rating_max', 'desc'],
            'bp' => ['bp', 'battle_point', 'desc'],
            'update' => ['update', 'updated_at', 'desc'],
        ];
    }

    #[DataProvider('sortProvider')]
    public function test_each_sort_maps_to_a_column_and_its_first_order(string $sort, string $column, string $firstOrder): void
    {
        $condition = $this->condition(['sort' => $sort]);

        $this->assertSame($sort, $condition->sort());
        $this->assertSame($column, $condition->column());
        $this->assertSame($firstOrder, $condition->order());
        $this->assertTrue($condition->isSorted());
    }

    public function test_explicit_order_is_used_for_each_sort(): void
    {
        foreach (array_keys(UserListCondition::SORTS) as $sort) {
            $this->assertSame('asc', $this->condition(['sort' => $sort, 'order' => 'asc'])->order(), $sort);
            $this->assertSame('desc', $this->condition(['sort' => $sort, 'order' => 'desc'])->order(), $sort);
        }
    }

    public function test_unknown_sort_falls_back_to_the_default_and_ignores_order(): void
    {
        foreach (['foo', 'user_id', 'RATING', 'rating;DROP TABLE users', ''] as $sort) {
            $condition = $this->condition(['sort' => $sort, 'order' => 'asc']);

            $this->assertSame('update', $condition->sort(), $sort);
            $this->assertSame('desc', $condition->order(), $sort);
            $this->assertFalse($condition->isSorted(), $sort);
        }
    }

    public function test_order_without_sort_is_ignored(): void
    {
        $condition = $this->condition(['order' => 'asc']);

        $this->assertSame('update', $condition->sort());
        $this->assertSame('desc', $condition->order());
        $this->assertFalse($condition->isSorted());
    }

    public function test_invalid_order_falls_back_to_the_first_order_of_the_sort(): void
    {
        $this->assertSame('desc', $this->condition(['sort' => 'rating', 'order' => 'foo'])->order());
        $this->assertSame('desc', $this->condition(['sort' => 'rating', 'order' => 'desc,1'])->order());
        $this->assertSame('asc', $this->condition(['sort' => 'name', 'order' => 'DESC'])->order());
        $this->assertSame('asc', $this->condition(['sort' => 'name', 'order' => ['desc']])->order());
    }

    public function test_array_parameters_are_treated_as_missing(): void
    {
        $condition = $this->condition(['sort' => ['id'], 'order' => ['asc'], 'q' => ['a']]);

        $this->assertSame('update', $condition->sort());
        $this->assertSame('desc', $condition->order());
        $this->assertNull($condition->keyword());
        $this->assertFalse($condition->isSorted());
    }

    public function test_keyword_is_trimmed_and_blank_means_no_search(): void
    {
        $this->assertSame('ali', $this->condition(['q' => '  ali  '])->keyword());
        $this->assertSame('a b', $this->condition(['q' => 'a b'])->keyword());
        $this->assertNull($this->condition(['q' => ''])->keyword());
        $this->assertNull($this->condition(['q' => '   '])->keyword());
        $this->assertNull($this->condition(['q' => null])->keyword());
    }

    public function test_keyword_is_cut_to_100_characters(): void
    {
        $this->assertSame(str_repeat('a', 100), $this->condition(['q' => str_repeat('a', 100)])->keyword());
        $this->assertSame(str_repeat('a', 100), $this->condition(['q' => str_repeat('a', 100).'b'])->keyword());
        // バイト数ではなく文字数で数える
        $this->assertSame(str_repeat('あ', 100), $this->condition(['q' => str_repeat('あ', 101)])->keyword());
    }

    public function test_keyword_with_invalid_utf8_means_no_search(): void
    {
        $this->assertNull($this->condition(['q' => "\xFF"])->keyword());
        $this->assertNull($this->condition(['q' => "ab\xE3\x81"])->keyword());
    }

    public function test_only_rating_and_max_put_current_version_users_first(): void
    {
        foreach (array_keys(UserListCondition::SORTS) as $sort) {
            $this->assertSame(
                in_array($sort, ['rating', 'max'], true),
                $this->condition(['sort' => $sort])->prioritizesCurrentVersion(),
                $sort
            );
        }
        $this->assertFalse($this->condition()->prioritizesCurrentVersion());
    }

    public function test_links_carry_no_sort_until_a_sort_is_chosen(): void
    {
        $condition = $this->condition();

        $this->assertSame([], $condition->query());
        $this->assertSame([], $condition->queryWithoutKeyword());
    }

    public function test_links_carry_the_chosen_sort(): void
    {
        $condition = $this->condition(['sort' => 'rating', 'order' => 'asc']);

        $this->assertSame(['sort' => 'rating', 'order' => 'asc'], $condition->query());
        $this->assertSame(['sort' => 'rating', 'order' => 'asc'], $condition->queryWithoutKeyword());
    }

    public function test_links_carry_the_normalized_order_when_order_was_omitted(): void
    {
        $this->assertSame(['sort' => 'rating', 'order' => 'desc'], $this->condition(['sort' => 'rating'])->query());
        $this->assertSame(['sort' => 'rating', 'order' => 'desc'], $this->condition(['sort' => 'rating', 'order' => 'foo'])->query());
    }

    public function test_links_carry_the_keyword_except_the_clear_link(): void
    {
        $this->assertSame(['q' => 'ali'], $this->condition(['q' => 'ali'])->query());
        $this->assertSame([], $this->condition(['q' => 'ali'])->queryWithoutKeyword());

        $condition = $this->condition(['sort' => 'bp', 'order' => 'desc', 'q' => 'ali']);
        $this->assertSame(['sort' => 'bp', 'order' => 'desc', 'q' => 'ali'], $condition->query());
        $this->assertSame(['sort' => 'bp', 'order' => 'desc'], $condition->queryWithoutKeyword());
    }

    public function test_links_never_carry_page_or_unknown_parameters(): void
    {
        $condition = $this->condition(['page' => '3', 'per_page' => '500', 'sort' => 'bp', 'q' => 'a']);

        $this->assertSame(['sort' => 'bp', 'order' => 'desc', 'q' => 'a'], $condition->query());
        $this->assertSame(['sort' => 'lv', 'order' => 'desc', 'q' => 'a'], $condition->sortQuery('lv'));
    }

    public function test_heading_of_another_column_uses_its_first_order(): void
    {
        $condition = $this->condition();

        foreach (['lv', 'rating', 'max', 'bp'] as $sort) {
            $this->assertSame(['sort' => $sort, 'order' => 'desc'], $condition->sortQuery($sort));
        }
        foreach (['id', 'name', 'trophy'] as $sort) {
            $this->assertSame(['sort' => $sort, 'order' => 'asc'], $condition->sortQuery($sort));
        }
    }

    public function test_heading_of_the_current_column_reverses_the_order(): void
    {
        // 既定（Update の降順）の状態でも、Update の見出しは逆向きになる
        $this->assertSame(['sort' => 'update', 'order' => 'asc'], $this->condition()->sortQuery('update'));

        $this->assertSame(['sort' => 'rating', 'order' => 'asc'], $this->condition(['sort' => 'rating', 'order' => 'desc'])->sortQuery('rating'));
        $this->assertSame(['sort' => 'rating', 'order' => 'desc'], $this->condition(['sort' => 'rating', 'order' => 'asc'])->sortQuery('rating'));
        // 既定と同じ並びに戻るときも sort と order を明示する
        $this->assertSame(['sort' => 'update', 'order' => 'desc'], $this->condition(['sort' => 'update', 'order' => 'asc'])->sortQuery('update'));
    }

    public function test_heading_keeps_the_keyword(): void
    {
        $condition = $this->condition(['sort' => 'rating', 'order' => 'desc', 'q' => 'ali']);

        $this->assertSame(['sort' => 'rating', 'order' => 'asc', 'q' => 'ali'], $condition->sortQuery('rating'));
        $this->assertSame(['sort' => 'name', 'order' => 'asc', 'q' => 'ali'], $condition->sortQuery('name'));
    }

    public function test_order_of_is_set_only_for_the_current_column(): void
    {
        $default = $this->condition();
        $this->assertSame('desc', $default->orderOf('update'));
        $this->assertNull($default->orderOf('rating'));

        $sorted = $this->condition(['sort' => 'name', 'order' => 'desc']);
        $this->assertSame('desc', $sorted->orderOf('name'));
        $this->assertNull($sorted->orderOf('update'));
    }
}
