<?php

namespace Tests\Feature;

use App\Facades\Slack;
use App\User;
use Tests\TestCase;

/**
 * ログアウトが CSRF 付きの POST でだけ行われることを確認する。
 * 旧版は GET /logout でログアウトしていたので、他サイトのリンクを踏ませるだけでログアウトさせられた。
 */
class LogoutTest extends TestCase
{
    private function user(): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'tester';
        $user->email = 'tester@example.test';

        return $user;
    }

    public function test_navbar_uses_a_post_form_with_csrf_token(): void
    {
        $html = $this->actingAs($this->user())->get('/')->assertStatus(200)->getContent();

        $this->assertStringContainsString('<form method="post" action="/logout">', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringNotContainsString('href="/logout"', $html);
    }

    public function test_get_logout_does_not_log_out(): void
    {
        $this->actingAs($this->user())->get('/logout')->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function test_post_logout_logs_out_and_redirects_to_the_logout_page(): void
    {
        // POST の応答は GET /logout へのリダイレクト（再読み込みで再送確認が出ないように）
        $this->actingAs($this->user())->post('/logout')->assertRedirect('/logout');

        $this->assertGuest();

        $this->get('/logout')->assertStatus(200)->assertViewIs('logout');
    }

    public function test_cross_site_post_without_token_is_rejected_and_keeps_the_session(): void
    {
        // テスト実行中は CSRF 検証が飛ばされる（env が testing）ので、このテストだけ有効にする
        $this->app['env'] = 'local';
        Slack::spy();

        $this->actingAs($this->user())
            ->post('/logout', [], ['Sec-Fetch-Site' => 'cross-site'])
            ->assertStatus(419);

        $this->assertAuthenticated();
    }
}
