<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItisPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_colleagues_if_they_exist()
    {
        $colleague = User::factory()->create(['Name' => 'Prof. Bob']);

        $this->actingAs($colleague)
            ->get('/itis')
            ->assertStatus(200)
            ->assertSee('Information Technology and Information Systems Department')
            ->assertSee('Prof. Bob');
    }

    /** @test */
    public function it_shows_no_colleagues_message_if_none_exist()
    {
        $this->actingAs(User::factory()->create())
            ->get('/itis')
            ->assertStatus(200)
            ->assertSee('No other colleagues in this department.');
    }

    /** @test */
    public function it_renders_search_and_chat_components()
    {
        $this->actingAs(User::factory()->create())
            ->get('/itis')
            ->assertSee('id="searchInput"', false)
            ->assertSee('AI Chat Assistant', false)
            ->assertSee('Click to chat with me!', false);
    }
}
