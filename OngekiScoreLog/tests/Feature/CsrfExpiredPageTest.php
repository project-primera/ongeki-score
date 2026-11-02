<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * CSRF トークン不一致（419）で、500 ページではなく「有効期限が切れた」案内のページが出ることを確認する。
 * CSRF の検証はテスト実行中は無効化されるので、フレームワークが変換した後の例外を描画に直接渡す。
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
}
