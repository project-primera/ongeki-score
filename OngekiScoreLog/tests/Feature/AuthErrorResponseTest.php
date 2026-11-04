<?php

namespace Tests\Feature;

use App\Facades\Slack;
use App\User;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Mockery;
use Tests\TestCase;

/**
 * 認証・認可に失敗したときの応答。
 * 旧版は例外ハンドラがどれも 500 ページにしていたので、未ログインは /login へ、管理者以外は 403 ページへ、
 * API のトークン無し・不正は 401 の JSON に直した。
 */
class AuthErrorResponseTest extends TestCase
{
    private function user(int $role): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'tester';
        $user->email = 'tester@example.test';
        $user->role = $role;

        return $user;
    }

    /** Passport のリソースサーバーを差し替えて、鍵ファイル無しで auth:api を通す */
    private function fakeResourceServer(): \Mockery\MockInterface
    {
        $server = Mockery::mock(ResourceServer::class);
        $this->app->instance(ResourceServer::class, $server);

        return $server;
    }

    public function test_guest_is_redirected_to_login_on_protected_pages(): void
    {
        Slack::spy();

        $this->get('/home')->assertRedirect('/login');
        $this->get('/admin/config')->assertRedirect('/login');

        // ログイン後に元のページへ戻れるよう、開こうとした URL がセッションに残ること
        $this->get('/admin')
            ->assertRedirect('/login')
            ->assertSessionHas('url.intended', url('/admin'));
    }

    public function test_non_admin_gets_the_403_page_on_admin_pages(): void
    {
        Slack::spy();

        $response = $this->actingAs($this->user(0))->get('/admin');
        $response->assertStatus(403);
        $this->assertStringContainsString('このページを表示する権限がありません', $response->getContent());

        $this->actingAs($this->user(0))->get('/admin/config')->assertStatus(403);
    }

    public function test_admin_still_opens_the_admin_page(): void
    {
        $this->actingAs($this->user(9))->get('/admin')->assertStatus(200);
    }

    public function test_api_without_token_gets_401_json(): void
    {
        Slack::spy();
        $this->fakeResourceServer();

        $this->get('/api/v2/user/update/status')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
        $this->post('/api/v2/user/update')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_api_with_an_invalid_token_gets_401_json(): void
    {
        Slack::spy();
        $this->fakeResourceServer()
            ->shouldReceive('validateAuthenticatedRequest')
            ->once()
            ->andThrow(OAuthServerException::accessDenied('The resource owner or authorization server denied the request.'));

        $this->get('/api/v2/user/update/status', ['Authorization' => 'Bearer invalid.token.value'])
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }
}
