<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComsciPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_colleagues_if_they_exist()
    {
        $colleague = User::factory()->create(['Name' => 'Prof. Alice']);

        $this->actingAs($colleague)
            ->get('/comsci')
            ->assertStatus(200)
            ->assertSee('Computer Science Department')
            ->assertSee('Prof. Alice');
    }

    /** @test */
    public function it_shows_no_colleagues_message_if_none_exist()
    {
        $this->actingAs(User::factory()->create())
            ->get('/comsci')
            ->assertStatus(200)
            ->assertSee('No other colleagues in this department.');
    }

    /** @test */
    public function it_renders_search_and_chat_components()
    {
        $this->actingAs(User::factory()->create())
            ->get('/comsci')
            ->assertSee('id="searchInput"', false) // search box
            ->assertSee('AI Chat Assistant', false) // chat overlay
            ->assertSee('Click to chat with me!', false); // button
    }
}
