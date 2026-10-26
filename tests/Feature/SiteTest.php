<?php

namespace Tests\Feature;

use App\Cms\PageBlocks;
use App\Filament\Pages\ManageSiteSettings;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Theme;
use Database\Seeders\StarterContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
        $this->seed(StarterContentSeeder::class);
        SiteSetting::set('launch', ['live' => true]);
    }

    public function test_visitors_see_coming_soon_page_until_site_is_live(): void
    {
        SiteSetting::set('launch', ['live' => false]);

        foreach (['/', '/about', '/blog', '/services/in-home-daytime-support', '/does-not-exist', '/not/a/route'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Great things are coming soon')
                ->assertDontSee('Book a Consult');
        }

        $this->get('/')->assertSee(route('filament.admin.auth.login'));

        $this->post('/contact-submit', ['name' => 'Sam', 'email' => 'sam@example.com', 'message' => 'Hi'])
            ->assertSee('Great things are coming soon');
        $this->assertSame(0, Inquiry::count());
    }

    public function test_signed_in_admins_preview_the_full_site_before_launch(): void
    {
        SiteSetting::set('launch', ['live' => false]);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Book a Consult')
            ->assertSee('visitors currently see the coming soon page');
    }

    public function test_admin_can_take_the_site_live(): void
    {
        SiteSetting::set('launch', ['live' => false]);

        Livewire::actingAs(User::factory()->create())
            ->test(ManageSiteSettings::class)
            ->set('data.launch.live', true)
            ->call('save')
            ->assertHasNoErrors();

        auth()->logout();

        $this->get('/')->assertOk()->assertSee('Book a Consult')->assertDontSee('Great things are coming soon');
    }

    public function test_starter_pages_render(): void
    {
        foreach (['/', '/about', '/services', '/faq', '/contact', '/blog', '/services/in-home-daytime-support', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_seeder_copies_stock_images_to_public_storage(): void
    {
        $service = Service::where('slug', 'in-home-daytime-support')->first();

        $this->assertSame('services/holding-baby-hand.jpg', $service->image);
        Storage::disk('public')->assertExists($service->image);
        $this->get('/')->assertSee($service->image_url, false);
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

    public function test_dark_theme_preset_gets_a_tinted_surface(): void
    {
        SiteSetting::set('theme', Theme::PRESETS['midnight_sea']['colors']);

        $this->get('/')->assertSee('--site-surface: color-mix(in oklab, #141d22 88%, white)', false);
    }

    public function test_light_theme_preset_keeps_a_white_surface(): void
    {
        SiteSetting::set('theme', Theme::PRESETS['lavender']['colors']);

        $this->get('/')->assertSee('--site-surface: #ffffff', false);
    }

    public function test_branding_name_and_motto_render(): void
    {
        SiteSetting::set('branding', [
            'site_name' => 'Harbourstone Postpartum Support',
            'logo_text' => 'Harbourstone',
            'motto' => 'Strong Foundations for New Beginnings',
        ]);

        $this->get('/')
            ->assertSee('<title>Harbourstone Postpartum Support | Strong Foundations for New Beginnings</title>', false)
            ->assertSee('>Harbourstone</span>', false);
    }

    public function test_admin_pages_load(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['/admin', '/admin/pages', '/admin/pages/1/edit', '/admin/services', '/admin/testimonials', '/admin/faqs', '/admin/posts', '/admin/inquiries', '/admin/users', ManageSiteSettings::getUrl()] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
