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
}
