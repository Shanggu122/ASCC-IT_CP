<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\InputHelperComSci;
use PHPUnit\Framework\Attributes\Test;

class InputHelperTestComSci extends TestCase
{
    #[Test]
    public function it_sanitizes_input_correctly(): void
    {
        $raw = "  <script>/*bad*/alert('x');</script>  ";
        $expected = "alert( x )";
        $this->assertEquals($expected, InputHelperComSci::sanitize($raw));
    }

    #[Test]
    public function it_limits_sanitized_input_to_50_characters(): void
    {
        $raw = str_repeat("abc123 ", 20);
        $sanitized = InputHelperComSci::sanitize($raw);
        $this->assertLessThanOrEqual(50, strlen($sanitized));
    }

    #[Test]
    public function it_filters_colleagues_by_name(): void
    {
        $colleagues = [
            ['Name' => 'Paul Cruz'],
            ['Name' => 'Jay Abaleta'],
        ];

        $filtered = InputHelperComSci::filterColleagues($colleagues, 'Paul');
        $this->assertCount(1, $filtered);
        $this->assertEquals('Paul Cruz', array_values($filtered)[0]['Name']);
    }

    #[Test]
    public function it_returns_empty_array_if_no_match(): void
    {
        $colleagues = [
            ['Name' => 'Paul Cruz'],
            ['Name' => 'Jay Abaleta'],
        ];

        $filtered = InputHelperComSci::filterColleagues($colleagues, 'Zelda');
        $this->assertEmpty($filtered);
    }

    #[Test]
    public function it_handles_special_characters_and_whitespace(): void
    {
        $raw = "Welcome to ASCC-IT. <script>";
        $expected = "Welcome to ASCC-IT.";
        $this->assertEquals($expected, InputHelperComSci::sanitize($raw));
    }

    #[Test]
    public function it_filters_with_unsanitized_search_input(): void
    {
        $colleagues = [
            ['Name' => 'Paul Cruz'],
            ['Name' => 'Jay Abaleta'],
        ];

        $filtered = InputHelperComSci::filterColleagues($colleagues, "<script>Paul Cruz</script>");
        $this->assertCount(1, $filtered);
        $this->assertEquals('Paul Cruz', array_values($filtered)[0]['Name']);
    }
}
