<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ComsciProfessorsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_filters_users_by_computer_science_department()
    {
        User::factory()->create(['name' => 'Alice', 'department' => 'Computer Science']);
        User::factory()->create(['name' => 'Bob', 'department' => 'Mathematics']);

        $colleagues = User::where('department', 'Computer Science')->get();

        $this->assertCount(1, $colleagues);
        $this->assertEquals('Alice', $colleagues->first()->name);
    }
}
