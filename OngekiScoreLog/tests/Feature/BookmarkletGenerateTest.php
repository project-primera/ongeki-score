<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

/**
 * ブックマークレットの生成が CSRF 付きの POST でだけ行われることを確認する。
 * 旧版は GET /bookmarklet/agree を Referer のパスだけで守っていて、他サイトの /bookmarklet からの遷移で再生成できた。
 * 実際の生成（トークンの失効と発行）は DB と oauth 鍵が要るので、ここでは扱わない。
 */
class BookmarkletGenerateTest extends TestCase
{
    private function user(): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'tester';
        $user->email = 'tester@example.test';

        return $user;
    }

    public function test_consent_page_has_a_post_form_with_csrf_token(): void
    {
        $html = $this->actingAs($this->user())->get('/bookmarklet')->assertStatus(200)->getContent();

        $this->assertStringContainsString('<form method="post" action="/bookmarklet/agree">', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringNotContainsString('href="/bookmarklet/agree"', $html);
    }

    public function test_get_agree_only_redirects_to_the_consent_page(): void
    {
        // 他サイトの /bookmarklet からの遷移（旧版の判定を通していた形）でも再生成せずに戻す
        $this->actingAs($this->user())
            ->get('/bookmarklet/agree', ['Referer' => 'https://evil.example/bookmarklet'])
            ->assertRedirect('/bookmarklet');
    }

    public function test_guest_is_asked_to_log_in(): void
    {
        $this->get('/bookmarklet')->assertStatus(200)->assertViewIs('require');
        $this->post('/bookmarklet/agree')->assertStatus(200)->assertViewIs('require');
    }
}
