<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use phpseclib4\Crypt\RSA;

abstract class TestCase extends BaseTestCase
{
    /** @var array<string, string>|null */
    private static ?array $passportKeys = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Vite のビルド成果物（public/build）が無くても view を描画できるようにする
        $this->withoutVite();

        // Passport の鍵（storage/oauth-*.key）は git 管理外なので、テスト用に作った鍵を設定で渡す。
        // 設定に鍵があればファイルは読まれないので、手元の鍵の有無で結果が変わらない
        config(self::passportKeys());
    }

    /**
     * テスト用の RSA 鍵。作り方は passport:keys と同じで、作るのは 1 プロセスにつき 1 回だけ
     *
     * @return array<string, string>
     */
    private static function passportKeys(): array
    {
        if (self::$passportKeys === null) {
            $key = RSA::createKey(2048);

            self::$passportKeys = [
                'passport.private_key' => (string) $key,
                'passport.public_key' => (string) $key->getPublicKey(),
            ];
        }

        return self::$passportKeys;
    }
}
