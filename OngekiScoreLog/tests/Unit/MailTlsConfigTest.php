<?php

namespace Tests\Unit;

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\TestCase;

/**
 * 旧版の MAIL_ENCRYPTION=tls（STARTTLS 必須）の意味が、新版の require_tls に引き継がれること。
 * 未設定なら従来どおり自動 STARTTLS（広告があれば使う）のまま。
 */
class MailTlsConfigTest extends TestCase
{
    private const KEYS = ['MAIL_ENCRYPTION', 'MAIL_REQUIRE_TLS'];

    /** 指定の環境変数だけを立てて config/mail.php を読み直し、smtp メーラーの設定を返す */
    private function smtpConfig(array $env): array
    {
        foreach (self::KEYS as $key) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }
        foreach ($env as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        try {
            $config = require __DIR__.'/../../config/mail.php';

            return $config['mailers']['smtp'];
        } finally {
            foreach (self::KEYS as $key) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
            }
        }
    }

    public function test_require_tls_follows_the_legacy_mail_encryption_setting(): void
    {
        $this->assertFalse($this->smtpConfig([])['require_tls']);
        $this->assertFalse($this->smtpConfig(['MAIL_ENCRYPTION' => 'null'])['require_tls']);
        $this->assertTrue($this->smtpConfig(['MAIL_ENCRYPTION' => 'tls'])['require_tls']);
        // 明示的な指定が優先される
        $this->assertFalse($this->smtpConfig(['MAIL_ENCRYPTION' => 'tls', 'MAIL_REQUIRE_TLS' => 'false'])['require_tls']);
        $this->assertTrue($this->smtpConfig(['MAIL_REQUIRE_TLS' => 'true'])['require_tls']);
    }

    public function test_require_tls_reaches_the_smtp_transport(): void
    {
        config(['mail.mailers.smtp.require_tls' => true]);
        $transport = app('mail.manager')->mailer('smtp')->getSymfonyTransport();
        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertTrue($transport->isTlsRequired());

        app('mail.manager')->purge('smtp');
        config(['mail.mailers.smtp.require_tls' => false]);
        $this->assertFalse(app('mail.manager')->mailer('smtp')->getSymfonyTransport()->isTlsRequired());
    }
}
