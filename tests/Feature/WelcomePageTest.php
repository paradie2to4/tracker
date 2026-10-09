<?php

namespace Tests\Feature;

use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_welcome_page_with_links_to_sign_up_and_log_in(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Know where every batch is,')
            ->assertSee(route('register'))
            ->assertSee(route('login'));
    }

    public function test_the_welcome_page_shows_real_platform_figures(): void
    {
        Batch::factory(3)->create();

        $this->get('/')
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['batches'] === 3 && $stats['locations'] === 3);
    }

    public function test_the_welcome_page_renders_with_an_empty_database(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['batches'] === 0);
    }
}
