<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\InputHelper;
use PHPUnit\Framework\Attributes\Test;

class InputHelperTest extends TestCase
{
    #[Test]
    public function it_sanitizes_input_correctly(): void
    {
        $raw = "  <script>/*bad*/alert('x');</script>  ";
        $expected = "script alert( x ) /script";
        $this->assertEquals($expected, InputHelper::sanitize($raw));
    }

    #[Test]
    public function it_limits_sanitized_input_to_50_characters(): void
    {
        $raw = str_repeat("abc123 ", 20); // 140+ characters
        $sanitized = InputHelper::sanitize($raw);
        $this->assertLessThanOrEqual(50, strlen($sanitized));
    }

    #[Test]
    public function it_filters_colleagues_by_name(): void
    {
        $colleagues = [
            ['Name' => 'Jake Libed'],
            ['Name' => 'Jay Abaleta'],
        ];

        $filtered = InputHelper::filterColleagues($colleagues, 'jake');
        $this->assertCount(1, $filtered);
        $this->assertEquals('Jake Libed', array_values($filtered)[0]['Name']);
    }

    #[Test]
    public function it_returns_empty_array_if_no_match(): void
    {
        $colleagues = [
            ['Name' => 'Jake Libed'],
            ['Name' => 'Jay Abaleta'],
        ];

        $filtered = InputHelper::filterColleagues($colleagues, 'Zelda');
        $this->assertEmpty($filtered);
    }

    #[Test]
    public function it_handles_special_characters_and_whitespace(): void
    {
        $raw = "Welcome to ASCC-IT. <script>";
        $expected = "Welcome to ASCC-IT. script";
        $this->assertEquals($expected, InputHelper::sanitize($raw));
    }

  #[Test]
public function it_filters_with_unsanitized_search_input(): void
{
    $colleagues = [
        ['Name' => 'Jake Libed'],
        ['Name' => 'Jay Abaleta'],
    ];

    // This input sanitizes to "Jake Libed"
    $filtered = InputHelper::filterColleagues($colleagues, "<script>Jake Libed</script>");
    $this->assertCount(1, $filtered);
    $this->assertEquals('Jake Libed', array_values($filtered)[0]['Name']);
}

}
