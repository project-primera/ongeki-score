<?php

namespace Tests\Feature;

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

    public function test_post_logout_logs_out_and_shows_the_logout_page(): void
    {
        $this->actingAs($this->user())->post('/logout')
            ->assertStatus(200)
            ->assertViewIs('logout');

        $this->assertGuest();
    }
}
