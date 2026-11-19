<?php

namespace Tests\Feature;

use App\Facades\Slack;
use App\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 管理者用のログ閲覧が、保存された内容をそのまま text/plain で返すことを確認する。
 * ログには訪問者が決められる文字列が入るので、HTML として解釈させない。
 * あわせて、管理者だけが開けることと、ファイルが無いときの応答を確認する。
 */
class AdminLogFileTest extends TestCase
{
    private function admin(): User
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'admin';
        $user->email = 'admin@example.test';
        $user->role = 9;

        return $user;
    }

    public function test_multiline_log_is_returned_exactly_as_saved(): void
    {
        Storage::fake();
        $content = "ip: 203.0.113.9\nUser agent: Mozilla/5.0\n\nRequest:\narray (\n  'q' => '譜面',\n)\n\n例外: テスト曲のエラー";
        Storage::put('log/Warning/1700000000_1', $content);

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_1');

        $response->assertStatus(200);
        $this->assertSame($content, $response->getContent());
    }

    public function test_single_line_log_is_returned_exactly_as_saved(): void
    {
        Storage::fake();
        $content = 'single line without newline';
        Storage::put('log/Warning/1700000000_4', $content);

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_4');

        $response->assertStatus(200);
        $this->assertSame($content, $response->getContent());
    }

    public function test_log_is_served_as_plain_text_with_nosniff_csp_and_no_store(): void
    {
        Storage::fake();
        Storage::put('log/Warning/1700000000_5', "ip: 203.0.113.9\nUser agent: Mozilla/5.0\n");

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_5');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; sandbox");
        // Symfony が private を補って指示子を並べ替えるので、値の完全一致では見ない
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_tags_in_the_log_are_returned_as_literal_text(): void
    {
        Storage::fake();
        $userAgent = "ip: 203.0.113.9\nUser agent: <script>log-marker-ua</script>\nReferer: N/A\n";
        $input = "Request:\narray (\n  'q' => '<img src=\"log-marker-input.png\">',\n)\n";
        Storage::put('log/Warning/1700000000_6', $userAgent);
        Storage::put('log/Warning/1700000000_7', $input);

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_6');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame($userAgent, $response->getContent());

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_7');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame($input, $response->getContent());
    }

    public function test_html_special_characters_are_returned_without_conversion(): void
    {
        Storage::fake();
        $content = "a & b < c > d \"e\" 'f' &amp; &lt;";
        Storage::put('log/Warning/1700000000_8', $content);

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_8');

        $response->assertStatus(200);
        $this->assertSame($content, $response->getContent());
    }

    public function test_empty_log_returns_200_with_an_empty_body(): void
    {
        Storage::fake();
        // 中身の無いログも実際に作られ、通知にリンクが載る
        Storage::put('log/Notice/1700000000_2', '');

        $response = $this->actingAs($this->admin())->get('/admin/log/Notice/1700000000_2');

        $response->assertStatus(200);
        $this->assertSame('', $response->getContent());
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_unreadable_log_returns_200_with_an_empty_body(): void
    {
        // 読めないファイルに対して Storage::get は null を返す。fake ではこの状態を作れないのでモックする
        Storage::shouldReceive('exists')->once()->with('log/Warning/1700000000_3')->andReturn(true);
        Storage::shouldReceive('get')->once()->with('log/Warning/1700000000_3')->andReturn(null);

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_3');

        $response->assertStatus(200);
        $this->assertSame('', $response->getContent());
    }

    public function test_invalid_utf8_bytes_are_returned_unchanged(): void
    {
        Storage::fake();
        // User-Agent は任意のバイトを含み得る
        $content = "ip: 203.0.113.9\nUser agent: \xFF\xFE bot \x80\nReferer: N/A\n";
        $this->assertFalse(mb_check_encoding($content, 'UTF-8'));
        Storage::put('log/Warning/1700000000_10', $content);

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_10');

        $response->assertStatus(200);
        $this->assertSame($content, $response->getContent());
    }

    public function test_text_sent_by_a_visitor_is_returned_as_plain_text_after_being_logged(): void
    {
        Slack::spy();
        Storage::fake();
        $marker = '<b>log-marker-query</b>';

        // 存在しないページへの要求 → 404 → Handler::report() が、入力を含む添付本文を Warning で送る
        $this->get('/no-such-page-for-log-view-test?q='.urlencode($marker))->assertStatus(404);

        $logged = null;
        Slack::shouldHaveReceived('Warning')->once()->withArgs(function (string $content, string $file) use (&$logged) {
            $logged = $file;

            return true;
        });
        $this->assertStringContainsString($marker, $logged);

        // 通知が保存するのと同じ場所に置いて、管理者として開く
        Storage::put('log/Warning/1700000000_11', $logged);
        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_11');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame($logged, $response->getContent());
    }

    public function test_guest_is_redirected_to_login_without_the_log(): void
    {
        Slack::spy();
        Storage::fake();
        Storage::put('log/Warning/1700000000_12', 'log-marker-guest');

        $response = $this->get('/admin/log/Warning/1700000000_12');

        $response->assertRedirect('/login');
        $this->assertStringNotContainsString('log-marker-guest', $response->getContent());
    }

    public function test_non_admin_gets_the_403_page_without_the_log(): void
    {
        Slack::spy();
        Storage::fake();
        Storage::put('log/Warning/1700000000_13', 'log-marker-non-admin');
        // 管理者は role が 7 以上
        $user = $this->admin();
        $user->role = 0;

        $response = $this->actingAs($user)->get('/admin/log/Warning/1700000000_13');

        $response->assertStatus(403);
        $this->assertStringNotContainsString('log-marker-non-admin', $response->getContent());
    }

    public function test_missing_log_returns_the_500_page(): void
    {
        Slack::spy();
        Storage::fake();

        $this->actingAs($this->admin())->get('/admin/log/Info/nonexistent.txt')->assertStatus(500);
    }

    public function test_path_pointing_at_a_directory_returns_200_with_an_empty_body(): void
    {
        Storage::fake();
        Storage::makeDirectory('log/Warning/1700000000_9');

        $response = $this->actingAs($this->admin())->get('/admin/log/Warning/1700000000_9');

        $response->assertStatus(200);
        $this->assertSame('', $response->getContent());
    }
}
