<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * サイドバーの「ランダムアクセス」(/random) が、公開ユーザーのページへリダイレクトすることを確認する。
 * 旧版は Collection を array_rand() に渡していて、常に TypeError で 500 になっていた。
 */
class RandomUserRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function addUser(int $id, int $private): void
    {
        DB::table('users')->insert([
            'id' => $id,
            'name' => 'user' . $id,
            'email' => 'user' . $id . '@example.test',
            'password' => 'x',
            'private' => $private,
        ]);
    }

    private function addStatus(int $userId, string $createdAt): void
    {
        DB::table('user_status')->insert([
            'user_id' => $userId,
            'trophy' => '',
            'level' => 1,
            'name' => 'player' . $userId,
            'battle_point' => 0,
            'rating' => 0,
            'rating_max' => 0,
            'money' => 0,
            'total_money' => 0,
            'total_play' => 0,
            'comment' => '',
            'friend_code' => 0,
            'unique_id' => 'unique' . $userId,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    public function test_redirects_to_a_public_user_page(): void
    {
        $this->addUser(1, 0);
        $this->addStatus(1, '2026-01-01 00:00:00');
        $this->addStatus(1, '2026-01-02 00:00:00');

        $this->get('/random')->assertRedirect('/user/1');
    }

    public function test_works_when_a_user_has_statuses_with_the_same_timestamp(): void
    {
        // 同じ時刻の行が 2 つあると、取得結果から重複が取り除かれて添字が飛ぶ
        $this->addUser(1, 0);
        $this->addStatus(1, '2026-01-02 00:00:00');
        $this->addStatus(1, '2026-01-02 00:00:00');
        $this->addUser(2, 0);
        $this->addStatus(2, '2026-01-01 00:00:00');

        for ($i = 0; $i < 20; $i++) {
            $location = $this->get('/random')->assertStatus(302)->headers->get('Location');
            $this->assertMatchesRegularExpression('#/user/[12]$#', $location);
        }
    }

    public function test_never_redirects_to_a_private_user(): void
    {
        $this->addUser(1, 0);
        $this->addStatus(1, '2026-01-01 00:00:00');
        $this->addUser(2, 1);
        $this->addStatus(2, '2026-01-02 00:00:00');

        for ($i = 0; $i < 20; $i++) {
            $this->get('/random')->assertRedirect('/user/1');
        }
    }

    public function test_redirects_to_top_when_there_is_no_user(): void
    {
        $this->get('/random')->assertRedirect('/');
    }

    public function test_redirects_to_top_when_every_user_is_private(): void
    {
        $this->addUser(1, 1);
        $this->addStatus(1, '2026-01-01 00:00:00');

        $this->get('/random')->assertRedirect('/');
    }
}
