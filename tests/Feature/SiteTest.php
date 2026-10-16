<?php

namespace Tests\Feature;

use App\Cms\PageBlocks;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\StarterContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(StarterContentSeeder::class);
    }

    public function test_starter_pages_render(): void
    {
        foreach (['/', '/about', '/services', '/faq', '/contact', '/blog', '/services/birth-doula-support', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_every_block_type_renders(): void
    {
        $blocks = collect(PageBlocks::all())
            ->map(fn ($block) => ['type' => $block->getName(), 'data' => ['heading' => 'Heading for '.$block->getName(), 'html' => '<p>x</p>']])
            ->all();

        Page::create(['title' => 'Everything', 'slug' => 'everything', 'blocks' => $blocks]);

        $this->get('/everything')->assertOk()->assertSee('Heading for hero');
    }

    public function test_unpublished_pages_and_unknown_slugs_404(): void
    {
        Page::where('slug', 'about')->update(['is_published' => false]);

        $this->get('/about')->assertNotFound();
        $this->get('/does-not-exist')->assertNotFound();
    }

    public function test_contact_form_saves_inquiry_and_notifies(): void
    {
        Notification::fake();

        $this->from('/contact')->post('/contact-submit', [
            'name' => 'Sam Parent',
            'email' => 'sam@example.com',
            'message' => 'Hello!',
            'due_date' => '2027-01-01',
        ])->assertRedirect()->assertSessionHas('inquiry_sent');

        $this->assertDatabaseHas('inquiries', ['email' => 'sam@example.com', 'status' => 'new']);
        Notification::assertCount(1);
    }

    public function test_contact_form_honeypot_drops_spam(): void
    {
        $this->post('/contact-submit', [
            'name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'spam', 'website' => 'http://spam.test',
        ])->assertRedirect();

        $this->assertSame(0, Inquiry::count());
    }

    public function test_theme_settings_reach_the_page(): void
    {
        SiteSetting::set('theme', ['primary' => '#123456']);

        $this->get('/')->assertSee('--site-primary: #123456', false);
    }

    public function test_admin_pages_load(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/admin', '/admin/pages', '/admin/pages/1/edit', '/admin/services', '/admin/testimonials', '/admin/faqs', '/admin/posts', '/admin/inquiries', '/admin/users', ManageSiteSettings::getUrl()] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
