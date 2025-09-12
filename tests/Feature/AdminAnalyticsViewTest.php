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
        // Check for key elements in the Blade view
        $response->assertSee('Consultation Analytics');
        $response->assertSee('Top Consultation Topics');
        $response->assertSee('Consultation Activity');
        $response->assertSee('Peak Consultation Days');
        $response->assertSee('topicsChart');
        $response->assertSee('activityChart');
        $response->assertSee('peakDaysChart');
    }
}