<?php

namespace Tests\Feature;

use App\Facades\Slack;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * スコア登録の API（v2 / v3）が通知に添付するログの内容を確認する。
 * 添付はファイルに保存されて後から開けるので、トークンや cookie、送信データなどリクエストの中身を書かない。
 */
class ApiUpdateNotificationLogTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'secret-api-token-for-log-test';

    private const COOKIE = 'secret-cookie-for-log-test';

    private const DATA = 'secret-payload-for-log-test';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Slack::spy();
        // コントローラが $_SERVER から直接読む
        $_SERVER['HTTP_USER_AGENT'] = 'phpunit';

        $this->user = new User();
        $this->user->name = 'tester';
        $this->user->email = 'tester@example.test';
        $this->user->password = 'secret';
        $this->user->role = 1;
        $this->user->save();
        Passport::actingAs($this->user);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_USER_AGENT']);

        parent::tearDown();
    }

    public function test_status_notification_log_has_only_the_client_and_the_user(): void
    {
        foreach (['v2', 'v3'] as $version) {
            $this->withTokenAndCookie()->get("/api/{$version}/user/update/status")->assertStatus(200);
        }

        Slack::shouldHaveReceived('Info')->twice()->withArgs(fn (string $content, string $file) => $this->assertLog($file));
    }

    public function test_status_notification_log_during_maintenance_has_only_the_client_and_the_user(): void
    {
        config(['env.is-maintenance-api-user-update' => true]);

        foreach (['v2', 'v3'] as $version) {
            $this->withTokenAndCookie()->get("/api/{$version}/user/update/status")->assertStatus(200);
        }

        Slack::shouldHaveReceived('Warning')->twice()->withArgs(fn (string $content, string $file) => $this->assertLog($file));
    }

    public function test_post_notification_log_during_maintenance_has_only_the_client_and_the_user(): void
    {
        config(['env.is-maintenance-api-user-update' => true]);

        foreach (['v2', 'v3'] as $version) {
            $this->withTokenAndCookie()
                ->post("/api/{$version}/user/update", ['hash' => 'hash-for-test', 'methodType' => '1', 'data' => self::DATA])
                ->assertStatus(200);
        }

        Slack::shouldHaveReceived('Warning')->twice()->withArgs(fn (string $content, string $file) => $this->assertLog($file));
    }

    /** ブックマークレットと同じく、トークンをヘッダに付けて送る。cookie は、要求に付いていた場合でもログに出ないことを確かめるために付ける */
    private function withTokenAndCookie(): static
    {
        return $this->withHeaders(['Authorization' => 'Bearer '.self::TOKEN])
            ->withUnencryptedCookie('cookie-for-log-test', self::COOKIE);
    }

    private function assertLog(string $file): bool
    {
        foreach ([self::TOKEN, self::COOKIE, self::DATA] as $secret) {
            $this->assertStringNotContainsString($secret, $file);
        }
        $this->assertSame(
            "ip: 127.0.0.1\nUser agent: phpunit\n\nUser:\nid: {$this->user->id}\nemail: tester@example.test\nrole: 1\n",
            $file
        );

        return true;
    }
}
