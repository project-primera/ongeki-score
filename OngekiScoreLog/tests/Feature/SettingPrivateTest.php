<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

/**
 * プロフィールの公開/非公開の切り替えが CSRF 付きの POST でだけ行われることを確認する。
 * 旧版は GET /setting/private と /setting/public だったので、他サイトのリンクを踏ませるだけで切り替えられた。
 * 実際の保存は DB が要るので、ここでは扱わない（GET が何も保存せずに戻ることは、DB の無いユーザーで保存が走れば失敗することで分かる）。
 */
class SettingPrivateTest extends TestCase
{
    private function user(int $private): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'tester';
        $user->email = 'tester@example.test';
        $user->private = $private;

        return $user;
    }

    public function test_setting_page_uses_post_forms_with_csrf_token(): void
    {
        $html = $this->actingAs($this->user(0))->get('/setting')->assertStatus(200)->getContent();
        $this->assertStringContainsString('<form method="post" action="/setting/private">', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringNotContainsString('href="/setting/private"', $html);

        $html = $this->actingAs($this->user(1))->get('/setting')->assertStatus(200)->getContent();
        $this->assertStringContainsString('<form method="post" action="/setting/public">', $html);
        $this->assertStringNotContainsString('href="/setting/public"', $html);
    }

    public function test_get_only_redirects_to_the_setting_page(): void
    {
        $this->actingAs($this->user(0))->get('/setting/private')->assertRedirect('/setting');
        $this->actingAs($this->user(1))->get('/setting/public')->assertRedirect('/setting');
    }

    public function test_guest_is_asked_to_log_in(): void
    {
        $this->post('/setting/private')->assertStatus(200)->assertViewIs('require');
        $this->post('/setting/public')->assertStatus(200)->assertViewIs('require');
    }
}
