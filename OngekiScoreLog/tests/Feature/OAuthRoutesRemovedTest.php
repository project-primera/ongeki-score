<?php

namespace Tests\Feature;

use App\Facades\Slack;
use Tests\TestCase;

/**
 * Passport の /oauth/* ルートは登録しない（AppServiceProvider の Passport::ignoreRoutes()）。
 * 404 になり、bot の走査として Slack にも通知しない（Handler::IGNORE_PAGES）。
 */
class OAuthRoutesRemovedTest extends TestCase
{
    public function test_no_oauth_routes_are_registered(): void
    {
        $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($route) => $route->uri());

        $this->assertEmpty($uris->filter(fn ($uri) => str_starts_with($uri, 'oauth'))->all());
    }

    public function test_oauth_endpoints_return_404_without_notifying_slack(): void
    {
        Slack::spy();

        $this->post('/oauth/token', ['grant_type' => 'client_credentials', 'client_id' => '1', 'client_secret' => 'x'])->assertStatus(404);
        $this->post('/oauth/token/refresh', ['refresh_token' => 'x'])->assertStatus(404);
        $this->get('/oauth/authorize?client_id=1&redirect_uri=https%3A%2F%2Fexample.test&response_type=code')->assertStatus(404);

        Slack::shouldNotHaveReceived('Warning');
        Slack::shouldNotHaveReceived('Error');
        Slack::shouldNotHaveReceived('Critical');
    }

    public function test_other_unknown_pages_are_still_notified(): void
    {
        Slack::spy();

        $this->get('/no-such-page-for-test')->assertStatus(404);

        Slack::shouldHaveReceived('Warning')->once();
    }
}
