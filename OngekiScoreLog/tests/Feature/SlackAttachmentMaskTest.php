<?php

namespace Tests\Feature;

use App\Facades\Slack;
use Tests\TestCase;

/**
 * 例外の Slack 通知に添付するリクエスト内容から、パスワード等の値が除かれていることを確認する。
 */
class SlackAttachmentMaskTest extends TestCase
{
    public function test_password_fields_are_masked_in_the_attachment(): void
    {
        Slack::spy();

        // 存在しないパスへの POST → 404 → Handler::report() が Warning を送る
        $this->post('/no-such-page-for-mask-test', [
            'email' => 'user@example.test',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
            'current_password' => 'secret-pass-0',
            'token' => 'secret-reset-token',
            'remember' => 'on',
        ])->assertStatus(404);

        Slack::shouldHaveReceived('Warning')->once()->withArgs(function (string $content, string $file) {
            $this->assertStringNotContainsString('secret-pass', $file);
            $this->assertStringNotContainsString('secret-reset-token', $file);
            $this->assertStringContainsString("'password' => '********'", $file);
            $this->assertStringContainsString("'email' => 'user@example.test'", $file);
            $this->assertStringContainsString("'remember' => 'on'", $file);

            return true;
        });
    }

    public function test_cookie_values_are_masked_in_the_attachment(): void
    {
        Slack::spy();

        // 404 はルート用ミドルウェア（EncryptCookies）より前に起きるので、cookie は受信した値のまま report() に届く
        $this->withUnencryptedCookie('remember_web_test', '1|secret-remember-token|secret-hash')
            ->withUnencryptedCookie('ongeki_session_test', 'secret-session-id')
            ->get('/no-such-page-for-cookie-test')
            ->assertStatus(404);

        Slack::shouldHaveReceived('Warning')->once()->withArgs(function (string $content, string $file) {
            $this->assertStringNotContainsString('secret-', $file);
            $this->assertStringContainsString("'remember_web_test' => '********'", $file);
            $this->assertStringContainsString("'ongeki_session_test' => '********'", $file);

            return true;
        });
    }

    public function test_decrypted_cookie_values_are_masked_in_the_attachment(): void
    {
        Slack::spy();

        // web ルートでは EncryptCookies が復号した後の平文が request に載る。
        // パスワード再設定メールの送信フォームの入力エラー → ValidationException → Warning
        $this->withCookie('remember_web_test', '1|secret-remember-token|secret-hash')
            ->post('/password/email', ['email' => 'not-an-email'])
            ->assertStatus(302);

        Slack::shouldHaveReceived('Warning')->once()->withArgs(function (string $content, string $file) {
            $this->assertStringContainsString('ValidationException', $content);
            $this->assertStringNotContainsString('secret-', $file);
            $this->assertStringContainsString("'remember_web_test' => '********'", $file);

            return true;
        });
    }
}
