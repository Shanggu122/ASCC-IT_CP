<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_analytics_view_renders_correctly()
    {
        $response = $this->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertSee('Expected Content');
        $response->assertSee('Another Expected Element');
    }
}