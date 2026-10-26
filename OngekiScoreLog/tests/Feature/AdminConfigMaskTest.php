<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use ReflectionMethod;
use Tests\TestCase;

/**
 * /admin/config の表示で秘密情報が隠れることを確認する。
 * 表示用の平坦化（AdminController::parseConfig）に、実際の config 全体を通して検査する。
 */
class AdminConfigMaskTest extends TestCase
{
    /** @var array<string, string|int> 隠すべき設定（ドット区切り）と、検査用に入れる値 */
    private const SECRETS = [
        'app.key' => 'base64:test-app-key',
        'database.connections.mysql.password' => 'db-password-mysql',
        'database.connections.mariadb.password' => 'db-password-mariadb',
        'database.connections.pgsql.password' => 'db-password-pgsql',
        'database.redis.default.password' => 'redis-password',
        'mail.mailers.smtp.host' => 'smtp.example.test',
        'mail.mailers.smtp.port' => 2525,
        'mail.mailers.smtp.username' => 'smtp-user',
        'mail.mailers.smtp.password' => 'smtp-password',
        'env.slack-webhook-url-error' => 'https://hooks.slack.test/error',
    ];

    public function test_secret_values_are_masked(): void
    {
        config(self::SECRETS);

        $rows = $this->parseAll();

        foreach (self::SECRETS as $key => $value) {
            $row = str_replace('.', '->', $key);
            $this->assertArrayHasKey($row, $rows);
            $this->assertSame('************', $rows[$row], $row);
        }

        // 別のキーにも秘密の値が出ていないこと
        foreach ($rows as $row => $shown) {
            $shown = is_scalar($shown) ? (string) $shown : '';
            foreach (self::SECRETS as $value) {
                $this->assertNotSame((string) $value, $shown, $row.' に秘密の値が出ている');
            }
        }
    }

    public function test_non_secret_values_are_shown(): void
    {
        config([
            'app.url' => 'https://ongeki-score.test',
            'database.connections.mysql.host' => 'db.example.test',
            'mail.mailers.smtp.scheme' => 'smtps',
        ]);

        $rows = $this->parseAll();

        $this->assertSame('https://ongeki-score.test', $rows['app->url']);
        $this->assertSame('db.example.test', $rows['database->connections->mysql->host']);
        $this->assertSame('smtps', $rows['mail->mailers->smtp->scheme']);
    }

    /** AdminController::GetConfig() と同じ手順で config 全体を平坦化する */
    private function parseAll(): array
    {
        $parse = new ReflectionMethod(AdminController::class, 'parseConfig');
        $controller = new AdminController();

        $rows = [];
        foreach (config()->all() as $key => $value) {
            $rows = $rows + $parse->invoke($controller, $key, $value);
        }

        return $rows;
    }
}
