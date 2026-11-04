<?php

namespace Tests\Feature;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Password;
use Mockery;
use Tests\TestCase;

/**
 * パスワード再設定の途中で例外が出たとき、Slack に添付されるトレース（$exception->__toString()）に
 * 平文のパスワードが残らないことを確認する。
 * laravel/ui の ResetsPasswords::reset() のクロージャと resetPassword() / setUserPassword() は $password に
 * SensitiveParameter を付けていないので、引数付きトレース（docker/php/zz-app.ini の zend.exception_ignore_args = Off）
 * では先頭 15 文字が残る。ResetPasswordController で上書きして属性を付けている。
 */
class PasswordResetTraceMaskTest extends TestCase
{
    public function test_plaintext_password_is_not_in_the_exception_trace_when_reset_fails(): void
    {
        $plain = 'SuperSecretPassw0rd!';

        // 本番イメージと同じ条件（引数付きトレース、文字列は 15 文字まで）
        $ignoreArgs = ini_get('zend.exception_ignore_args');
        $maxLen = ini_get('zend.exception_string_param_max_len');
        ini_set('zend.exception_ignore_args', '0');
        ini_set('zend.exception_string_param_max_len', '15');

        try {
            // broker はトークンの照合を飛ばして再設定処理（callback）をそのまま呼ぶ。
            // テスト用 DB に users テーブルが無いので save() で QueryException になる
            $broker = Mockery::mock(PasswordBroker::class);
            $broker->shouldReceive('reset')->once()->andReturnUsing(function (array $credentials, Closure $callback) {
                $callback(new User(), $credentials['password']);

                return Password::PASSWORD_RESET;
            });
            Password::shouldReceive('broker')->andReturn($broker);

            $this->withoutExceptionHandling();

            try {
                $this->post('/password/reset', [
                    'token' => 'dummy-token',
                    'email' => 'user@example.test',
                    'password' => $plain,
                    'password_confirmation' => $plain,
                ]);
                $this->fail('再設定処理で例外が出る前提のテスト');
            } catch (QueryException $e) {
                $trace = $e->__toString();

                // 引数付きトレースで再設定のフレームが載っていること（前提の確認）
                $this->assertStringContainsString('resetPassword(', $trace);
                $this->assertStringContainsString('SensitiveParameterValue', $trace);
                // 平文（先頭 15 文字）がどのフレームにも残っていないこと
                $this->assertStringNotContainsString(substr($plain, 0, 15), $trace);
            }
        } finally {
            ini_set('zend.exception_ignore_args', $ignoreArgs === false ? '0' : $ignoreArgs);
            ini_set('zend.exception_string_param_max_len', $maxLen === false ? '15' : $maxLen);
        }
    }
}
