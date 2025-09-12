<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagesViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_view_renders_correctly()
    {
        // Provide fake professors data to the view
        $professors = [
            (object)[
                'name' => 'Prof. John Doe',
                'profile_picture' => null,
                'last_message' => 'Hello student!',
                'last_sender' => 'professor',
                'last_message_time' => now()->subMinutes(5)->toDateTimeString(),
                'booking_id' => 1,
            ]
        ];

        // Render the view directly with data
        $view = $this->view('messages', ['professors' => $professors]);

        $view->assertSee('Inbox');
        $view->assertSee('Prof. John Doe');
        $view->assertSee('No messages yet', false); // fallback if no last_message
        $view->assertSee('Type a message...');
        $view->assertSee('Video Call');
    }
}