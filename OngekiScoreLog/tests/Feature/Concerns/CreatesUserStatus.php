<?php

namespace Tests\Feature\Concerns;

use App\User;
use App\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

/**
 * ユーザー一覧のテスト用に、ユーザーとステータス（user_status）を作り、表示された一覧を読み取る。
 */
trait CreatesUserStatus
{
    private int $userSequence = 0;

    /** user_status の 1 行分の値。指定しなかった列は既定値で埋める */
    private function statusRow(int $userId, array $attributes): array
    {
        $at = $attributes['updated_at'] ?? '2026-06-01 00:00:00';

        return array_merge([
            'user_id' => $userId,
            'trophy' => 'trophy',
            'level' => 1,
            'name' => 'player'.$userId,
            'battle_point' => 0,
            'rating' => 0,
            'rating_max' => 0,
            'money' => 0,
            'total_money' => 0,
            'total_play' => 0,
            'comment' => '',
            'friend_code' => 0,
            'unique_id' => 'test',
            'created_at' => $at,
            'updated_at' => $at,
        ], $attributes);
    }

    /**
     * ユーザーを 1 人作る。$status が配列ならその内容のステータスを 1 行付ける（null ならステータスなし）。
     */
    protected function createUser(?array $status = [], int $private = 0, int $role = 1): User
    {
        $this->userSequence++;

        $user = new User();
        $user->name = 'account'.$this->userSequence;
        $user->email = 'account'.$this->userSequence.'@example.test';
        $user->password = 'secret';
        $user->role = $role;
        $user->private = $private;
        $user->save();

        if ($status !== null) {
            $this->addStatus($user, $status);
        }

        return $user;
    }

    /** 既存のユーザーにステータスを 1 行足す（後から足した行ほど id が大きい） */
    protected function addStatus(User $user, array $attributes = []): UserStatus
    {
        return UserStatus::create($this->statusRow($user->id, $attributes));
    }

    /**
     * 公開ユーザーをまとめて作り、作った順のユーザー ID を返す。
     * $status は「何人目か（0 始まり）」を受け取ってステータスの値を返す。
     *
     * @return int[]
     */
    protected function createUsers(int $count, ?callable $status = null): array
    {
        $users = [];
        $statuses = [];
        $ids = [];
        $nextId = (int) DB::table('users')->max('id') + 1;

        for ($i = 0; $i < $count; $i++) {
            $this->userSequence++;
            $id = $nextId + $i;
            $ids[] = $id;
            $users[] = [
                'id' => $id,
                'name' => 'account'.$this->userSequence,
                'email' => 'account'.$this->userSequence.'@example.test',
                'password' => 'secret',
                'role' => 1,
                'private' => 0,
            ];
            $statuses[] = $this->statusRow($id, $status === null ? [] : $status($i));
        }

        foreach (array_chunk($users, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }
        foreach (array_chunk($statuses, 500) as $chunk) {
            DB::table('user_status')->insert($chunk);
        }

        return $ids;
    }

    /** 一覧の表に出ているユーザー ID を、表示順に返す */
    protected function listedUserIds(TestResponse $response): array
    {
        preg_match_all('#href="/user/(\d+)"#', $this->tableBody($response), $matches);

        return array_map('intval', $matches[1]);
    }

    /** 一覧の表の各行を、セルの文字列の配列にして返す */
    protected function listedRows(TestResponse $response): array
    {
        preg_match_all('#<tr[^>]*>(.*?)</tr>#s', $this->tableBody($response), $rows);

        return array_map(function (string $row): array {
            preg_match_all('#<td[^>]*>(.*?)</td>#s', $row, $cells);

            return array_map(fn (string $cell): string => trim(html_entity_decode(strip_tags($cell))), $cells[1]);
        }, $rows[1]);
    }

    /**
     * ページ内のリンク（a 要素）を、属性と文字列の配列にして返す。
     * href などの属性値は HTML エスケープを戻した形（&amp; → &）にする。
     *
     * @return array<int, array<string, string>> 各要素は属性名 => 値。リンクの文字列は 'text'
     */
    protected function anchors(TestResponse $response, ?string $html = null): array
    {
        preg_match_all('#<a\b([^>]*)>(.*?)</a>#s', $html ?? $response->getContent(), $matches, PREG_SET_ORDER);

        return array_map(function (array $match): array {
            preg_match_all('#([\w-]+)(?:="([^"]*)")?#', $match[1], $attributes, PREG_SET_ORDER);

            $anchor = ['text' => trim(html_entity_decode(strip_tags($match[2])))];
            foreach ($attributes as $attribute) {
                $anchor[$attribute[1]] = html_entity_decode($attribute[2] ?? '');
            }

            return $anchor;
        }, $matches);
    }

    /** ページ送り（nav.pagination）の中のリンクを、1 つ目の nav について返す */
    protected function pagerAnchors(TestResponse $response): array
    {
        if (!preg_match('#<nav class="pagination[^>]*>(.*?)</nav>#s', $response->getContent(), $match)) {
            return [];
        }

        return $this->anchors($response, $match[1]);
    }

    private function tableBody(TestResponse $response): string
    {
        if (!preg_match('#<tbody[^>]*>(.*?)</tbody>#s', $response->getContent(), $match)) {
            return '';
        }

        return $match[1];
    }
}
