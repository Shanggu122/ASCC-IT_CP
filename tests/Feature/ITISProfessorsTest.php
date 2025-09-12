<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ItisProfessorsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_only_users_from_itis_department()
    {
        User::factory()->create(['name' => 'Jane', 'department' => 'Information Technology and Information Systems']);
        User::factory()->create(['name' => 'Mark', 'department' => 'Computer Science']);

        $itisColleagues = User::where('department', 'Information Technology and Information Systems')->get();

        $this->assertCount(1, $itisColleagues);
        $this->assertEquals('Jane', $itisColleagues->first()->name);
    }
}
