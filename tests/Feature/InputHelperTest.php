<?php 

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\InputHelper;

class InputHelperTest extends TestCase
{
    /** @test */
    public function it_sanitizes_input_correctly()
    {
        $raw = "  <script>/*bad*/alert('x');</script>  ";
        $expected = "script alert x";
        $this->assertEquals($expected, InputHelper::sanitize($raw));
    }

    /** @test */
    public function it_limits_sanitized_input_to_50_characters()
    {
        $raw = str_repeat("a", 100);
        $sanitized = InputHelper::sanitize($raw);
        $this->assertLessThanOrEqual(50, strlen($sanitized));
    }

    /** @test */
    public function it_filters_colleagues_by_name()
    {
        $colleagues = [
            ['Name' => 'Jessie Alamil'],
            ['Name' => 'Jay Abaleta'],
            ['Name' => 'Niña Ana Marie Jocelyn Sales'],
        ];

        $filtered = InputHelper::filterColleagues($colleagues, 'bob');
        $this->assertCount(1, $filtered);
        $this->assertEquals('Bob Smith', array_values($filtered)[0]['Name']);
    }

    /** @test */
    public function it_returns_empty_array_if_no_match()
    {
        $colleagues = [
            ['Name' => 'Alice Johnson'],
            ['Name' => 'Bob Smith'],
        ];

        $filtered = InputHelper::filterColleagues($colleagues, 'Zelda');
        $this->assertEmpty($filtered);
    }
}
