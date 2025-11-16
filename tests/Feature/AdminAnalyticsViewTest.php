<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAnalyticsViewTest extends TestCase
{
    public function test_admin_analytics_page_has_expected_elements(): void
    {
        // Bypass admin auth and any other middleware so we can render the view directly
        $this->withoutMiddleware();

        // Use the named route defined in routes/web.php (path: /admin-analytics)
        $response = $this->get(route('admin.analytics'));

        $response->assertOk();

        // Page title exists in <title>
        $response->assertSee('Admin Analytics');

        // Section headers present in the Blade
        $response->assertSee('ITIS Consultation Activity');
        $response->assertSee('ITIS Peak Days');
        $response->assertSee('ComSci Consultation Activity');
        $response->assertSee('ComSci Peak Days');

        // Canvas elements and legend containers for both departments
        $response->assertSee('id="itisTopicsChart"', false);
        $response->assertSee('id="itisActivityChart"', false);
        $response->assertSee('id="itisPeakDaysChart"', false);
        $response->assertSee('id="comsciTopicsChart"', false);
        $response->assertSee('id="comsciActivityChart"', false);
        $response->assertSee('id="comsciPeakDaysChart"', false);
        $response->assertSee('id="itisTopicLegend"', false);
        $response->assertSee('id="comsciTopicLegend"', false);

        // CSRF meta and Chart.js script URL
        $response->assertSee('name="csrf-token"', false);
        $response->assertSee('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js', false);
    }
}
