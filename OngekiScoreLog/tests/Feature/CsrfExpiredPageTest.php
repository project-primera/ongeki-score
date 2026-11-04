<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use App\Facades\Slack;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * CSRF トークン不一致（419）で、500 ページではなく「有効期限が切れた」案内のページが出ることを確認する。
 * CSRF の検証はテスト実行中（env が testing）は飛ばされるので、
 * 描画だけを見るテストはフレームワークが変換した後の例外を直接渡し、
 * 一気通貫のテストは env を切り替えて検証を有効にする。
 */
class CsrfExpiredPageTest extends TestCase
{
    public function test_token_mismatch_renders_the_page_expired_view(): void
    {
        $original = new TokenMismatchException('CSRF token mismatch.');
        $converted = new HttpException(419, $original->getMessage(), $original);

        $response = Handler::render(Request::create('/setting/private', 'POST'), $converted);

        $this->assertNotNull($response);
        $this->assertSame(419, $response->getStatusCode());
        $this->assertStringContainsString('ページの有効期限が切れました', $response->getContent());
        $this->assertStringContainsString('再読み込み', $response->getContent());
    }

    public function test_cross_site_post_without_token_gets_the_page_end_to_end(): void
    {
        $this->app['env'] = 'local';
        Slack::spy();

        $user = new User();
        $user->id = 1;
        $user->name = 'tester';
        $user->email = 'tester@example.test';
        $user->private = 0;

        $response = $this->actingAs($user)->post('/setting/private', [], ['Sec-Fetch-Site' => 'cross-site']);

        $response->assertStatus(419);
        $this->assertStringContainsString('ページの有効期限が切れました', $response->getContent());
        // 拒否されたので設定は変わっていない
        $this->assertSame(0, $user->private);
    }
}
