<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Helpers\InputHelperITIS;
use PHPUnit\Framework\Attributes\Test;

class InputHelperITISTest extends TestCase
{
    #[Test]
    public function it_sanitizes_script_and_special_characters(): void
    {
        $raw = "<script>/*bad*/alert('x');</script>";
        $expected = "alert( x )";
        $this->assertEquals($expected, InputHelperITIS::sanitize($raw));
    }

    #[Test]
    public function it_limits_sanitized_input_to_50_characters(): void
    {
        $raw = str_repeat("abc123 ", 20);
        $sanitized = InputHelperITIS::sanitize($raw);
        $this->assertLessThanOrEqual(50, strlen($sanitized));
    }

    #[Test]
    public function it_filters_colleagues_by_name(): void
    {
        $colleagues = [
            ['Name' => 'Anette Daligcon'],
            ['Name' => 'Gloria Dela Cruz'],
        ];

        $filtered = InputHelperITIS::filterColleagues($colleagues, 'Gloria');
        $this->assertCount(1, $filtered);
        $this->assertEquals('Gloria Dela Cruz', array_values($filtered)[0]['Name']);
    }

    #[Test]
    public function it_returns_all_if_search_is_empty(): void
    {
        $colleagues = [
            ['Name' => 'Felnita Tan'],
            ['Name' => 'Carmelita Benito'],
        ];

        $filtered = InputHelperITIS::filterColleagues($colleagues, '   ');
        $this->assertCount(2, $filtered);
    }

    #[Test]
    public function it_returns_empty_array_if_no_match(): void
    {
        $colleagues = [
            ['Name' => 'Amado Sapit III'],
            ['Name' => 'Charlene Vergara'],
        ];

        $filtered = InputHelperITIS::filterColleagues($colleagues, 'Zelda');
        $this->assertEmpty($filtered);
    }
}
