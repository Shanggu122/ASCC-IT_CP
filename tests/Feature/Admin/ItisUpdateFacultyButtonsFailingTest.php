<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

/**
 * This test is intentionally designed to fail to demonstrate how the
 * test runner reports failures. Keep it out of CI or run it explicitly
 * when you want to show a failing example.
 *
 * Run just this file:
 *   php artisan test tests/Feature/Admin/ItisUpdateFacultyButtonsFailingTest.php
 */
class ItisUpdateFacultyButtonsFailingTest extends TestCase
{
    public function test_intentional_failure_demo(): void
    {
        $this->fail(
            "Intentional failure demo: This test fails on purpose so you can see red output and the message above.",
        );
    }
}
