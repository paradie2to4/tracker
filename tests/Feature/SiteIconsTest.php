<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_layout_links_the_site_icons(): void
    {
        $pages = [
            $this->get('/'),
            $this->get('/login'),
            $this->actingAs(User::factory()->create())->get('/dashboard'),
        ];

        foreach ($pages as $response) {
            $response->assertOk()
                ->assertSee(asset('favicon.svg'), false)
                ->assertSee(asset('favicon.ico'), false)
                ->assertSee(asset('site.webmanifest'), false);
        }
    }

    public function test_the_icon_files_exist(): void
    {
        foreach (['favicon.ico', 'favicon.svg', 'apple-touch-icon.png', 'icon-192.png', 'icon-512.png', 'site.webmanifest'] as $file) {
            $this->assertGreaterThan(0, filesize(public_path($file)), "{$file} is missing or empty.");
        }

        $manifest = json_decode(file_get_contents(public_path('site.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('ProductSphere', $manifest['name']);
    }
}
