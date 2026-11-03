<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * 管理ページのキャッシュ操作と統計生成が CSRF 付きの POST でだけ行われることを確認する。
 * 旧版は GET だったので、ログイン中の管理者に他サイトのリンクを踏ませるだけでキャッシュ消去や重い集計を走らせられた。
 */
class AdminActionsTest extends TestCase
{
    private function admin(): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'admin';
        $user->email = 'admin@example.test';
        $user->role = 9;

        return $user;
    }

    public function test_admin_page_uses_post_forms_with_csrf_token(): void
    {
        $html = $this->actingAs($this->admin())->get('/admin')->assertStatus(200)->getContent();

        $this->assertStringContainsString('<form method="post" action="/admin/apply/all/clear">', $html);
        $this->assertStringContainsString('<form method="post" action="/admin/apply/cache">', $html);
        $this->assertStringContainsString('<form method="post" action="/admin/generate/battle-score">', $html);
        $this->assertStringContainsString('<form method="post" action="/admin/generate/over-damage">', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringNotContainsString('href="/admin/apply/', $html);
        $this->assertStringNotContainsString('href="/admin/generate/', $html);
    }

    public function test_get_only_redirects_to_the_admin_page_without_running_anything(): void
    {
        Artisan::shouldReceive('call')->never();

        $this->actingAs($this->admin())->get('/admin/apply/all/clear')->assertRedirect('/admin');
        $this->actingAs($this->admin())->get('/admin/apply/cache')->assertRedirect('/admin');
        // 集計は DB が要るので、実行されていればここで失敗する
        $this->actingAs($this->admin())->get('/admin/generate/battle-score')->assertRedirect('/admin');
        $this->actingAs($this->admin())->get('/admin/generate/over-damage')->assertRedirect('/admin');
    }

    public function test_post_runs_the_action_and_reports_the_result(): void
    {
        Artisan::shouldReceive('call')->once()->with('cache:clear')->andReturn(0);

        $this->actingAs($this->admin())->post('/admin/apply/cache')
            ->assertRedirectContains('/admin?message=cache:clear');
    }

    public function test_post_with_an_unknown_path_runs_nothing(): void
    {
        Artisan::shouldReceive('call')->never();

        $this->actingAs($this->admin())->post('/admin/apply/bogus')
            ->assertRedirectContains('/admin?message=');
    }
}
