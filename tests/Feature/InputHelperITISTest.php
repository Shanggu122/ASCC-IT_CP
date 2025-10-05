<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Helpers\InputHelperITIS;

class InputHelperITISTest extends TestCase
{
    public function test_sanitizes_script_and_special_characters(): void
    {
        $raw = "<script>/*bad*/alert('x');</script>";
        $expected = "alert( x )";
        $this->assertEquals($expected, InputHelperITIS::sanitize($raw));
    }

    public function test_limits_sanitized_input_to_50_characters(): void
    {
        $raw = str_repeat("abc123 ", 20);
        $sanitized = InputHelperITIS::sanitize($raw);
        $this->assertLessThanOrEqual(50, strlen($sanitized));
    }

    public function test_filters_colleagues_by_name(): void
    {
        $colleagues = [["Name" => "Anette Daligcon"], ["Name" => "Gloria Dela Cruz"]];

        $filtered = InputHelperITIS::filterColleagues($colleagues, "Gloria");
        $this->assertCount(1, $filtered);
        $this->assertEquals("Gloria Dela Cruz", array_values($filtered)[0]["Name"]);
    }

    public function test_returns_all_if_search_is_empty(): void
    {
        $colleagues = [["Name" => "Felnita Tan"], ["Name" => "Carmelita Benito"]];

        $filtered = InputHelperITIS::filterColleagues($colleagues, "   ");
        $this->assertCount(2, $filtered);
    }

    public function test_returns_empty_array_if_no_match(): void
    {
        $colleagues = [["Name" => "Amado Sapit III"], ["Name" => "Charlene Vergara"]];

        $filtered = InputHelperITIS::filterColleagues($colleagues, "Zelda");
        $this->assertEmpty($filtered);
    }
}
