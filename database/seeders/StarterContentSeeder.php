<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Starter content so the site looks complete on day one. Everything here is
 * editable in the admin; placeholder copy is written to be rewritten.
 *
 * Safe to re-run: records are matched on slug / question and only created
 * when missing, so edits made in the admin are never overwritten.
 */
class StarterContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->settings();
        $services = $this->services();
        $this->testimonials($services);
        $this->faqs();
        $this->posts();
        $this->pages($services);
    }

    protected function settings(): void
    {
        $defaults = [
            'branding' => [
                'site_name' => 'Harbourstone Postpartum Support',
                'logo_text' => 'Harbourstone',
                'tagline' => 'Postpartum Support',
                'motto' => 'Strong Foundations for New Beginnings',
            ],
            'theme' => [
                'preset' => 'harbourstone',
            ],
            'topbar' => [
                'enabled' => true,
                'text' => 'In-home postpartum support across Greater Victoria & Vancouver Island',
                'link_label' => 'Book a free consult',
                'link_url' => '/contact',
            ],
            'navigation' => [
                'items' => [
                    ['label' => 'Home', 'url' => '/', 'external' => false, 'children' => []],
                    ['label' => 'About', 'url' => '/about', 'external' => false, 'children' => []],
                    ['label' => 'Support', 'url' => '/services', 'external' => false, 'children' => [
                        ['label' => 'In-Home Daytime Support', 'url' => '/services/in-home-daytime-support'],
                        ['label' => 'Overnight Support', 'url' => '/services/overnight-support'],
                        ['label' => 'Postpartum Planning', 'url' => '/services/postpartum-planning'],
                    ]],
                    ['label' => 'FAQ', 'url' => '/faq', 'external' => false, 'children' => []],
                    ['label' => 'Blog', 'url' => '/blog', 'external' => false, 'children' => []],
                    ['label' => 'Contact', 'url' => '/contact', 'external' => false, 'children' => []],
                ],
                'show_cta' => true,
                'cta_label' => 'Book a Consult',
                'cta_url' => '/contact',
            ],
            'contact' => [
                'email' => 'hello@example.com',
                'phone' => '(250) 555-0123',
                'service_area' => 'Victoria, Saanich, Langford & surrounding areas',
                'hours' => 'Daytime & overnight visits · Inquiries answered within 1 business day',
                'thank_you' => 'Thank you for reaching out! I\'ll be in touch within one business day.',
            ],
            'social' => [
                'instagram' => 'https://instagram.com/',
                'facebook' => 'https://facebook.com/',
            ],
            'footer' => [
                'about' => 'Calm, practical, in-home care for new parents in the weeks after baby arrives — so you can rest, heal and settle in with confidence.',
                'copyright' => '© {year} {name}. All rights reserved.',
            ],
            'seo' => [
                'share_image' => $this->stockImage('home-hero.jpg', 'branding/share-image.jpg'),
                'description' => 'Harbourstone Postpartum Support offers in-home daytime and overnight care for new families across Greater Victoria, BC. Strong foundations for new beginnings.',
            ],
        ];

        foreach ($defaults as $key => $value) {
            if (! SiteSetting::where('key', $key)->exists()) {
                SiteSetting::set($key, $value);
            }
        }
    }

    /**
     * @return array<string, Service>
     */
    protected function services(): array
    {
        $services = [
            [
                'slug' => 'in-home-daytime-support',
                'title' => 'In-Home Daytime Support',
                'tagline' => 'Rest, recover and find your rhythm',
                'icon' => 'home',
                'image' => $this->stockImage('holding-baby-hand.jpg', 'services/holding-baby-hand.jpg'),
                'summary' => 'Hands-on help at home in the weeks after baby arrives — feeding support, newborn care, a warm meal and time for you to rest and heal.',
                'price_label' => 'From $40 / hour',
                'button_label' => 'Book a Free Consult',
                'button_url' => '/contact',
                'features' => [
                    'Feeding support (breast, chest or bottle)',
                    'Newborn care & soothing',
                    'Time for you to shower, nap or eat',
                    'Light meal prep & tidying',
                    'Sibling & partner support',
                    'Emotional check-ins',
                ],
                'description' => '<p>The early weeks with a new baby are tender, exhausting and often overwhelming. Daytime support gives you a calm, experienced person in your home so you can rest, heal and find your feet as a family.</p><h2>What a visit looks like</h2><p>Every visit is shaped around what you need that day — help with feeding, a chance to sleep while baby is cared for, a warm meal, folded laundry, or simply someone to talk through how you\'re feeling.</p><p>Visits are typically three to four hours, as often as feels right for your family.</p>',
            ],
            [
                'slug' => 'overnight-support',
                'title' => 'Overnight Support',
                'tagline' => 'Restful nights for the whole family',
                'icon' => 'moon',
                'image' => $this->stockImage('sleeping-newborn.jpg', 'services/sleeping-newborn.jpg'),
                'summary' => 'Overnight care so you can get the sleep your body needs to recover, knowing baby is in gentle, experienced hands.',
                'price_label' => 'From $45 / hour',
                'button_label' => 'Ask About Availability',
                'button_url' => '/contact',
                'features' => [
                    'Baby care through the night',
                    'Feeding support or bottle feeds',
                    'Gentle, responsive settling',
                    'Bottle & pump part cleaning',
                    'Morning handover & notes',
                ],
                'description' => '<p>Sleep is one of the foundations of recovery. With overnight support I care for your baby through the night — bringing baby to you for feeds or giving bottles, settling, changing and soothing — so you can wake up rested.</p><p>Overnight shifts are usually eight to ten hours and can be booked a few nights a week or for a stretch while you recover.</p>',
            ],
            [
                'slug' => 'postpartum-planning',
                'title' => 'Postpartum Planning',
                'tagline' => 'Prepare for the fourth trimester',
                'icon' => 'book-open',
                'image' => $this->stockImage('postpartum-planning.jpg', 'services/postpartum-planning.jpg'),
                'summary' => 'A relaxed session before baby arrives to build your postpartum plan — support, meals, recovery, sleep and local resources.',
                'price_label' => '$150 per session',
                'button_label' => 'Book a Session',
                'button_url' => '/contact',
                'features' => [
                    'Personalized postpartum plan',
                    'Recovery & rest planning',
                    'Feeding preparation',
                    'Meal & household planning',
                    'Local resource list',
                ],
                'description' => '<p>So much preparation focuses on the birth, and so little on the weeks that follow. Together we\'ll build a simple, realistic plan for your fourth trimester so you feel ready for life at home with your new baby.</p>',
            ],
        ];

        $models = [];

        foreach ($services as $i => $service) {
            $models[$service['slug']] = Service::firstOrCreate(
                ['slug' => $service['slug']],
                $service + ['sort_order' => $i, 'is_featured' => true, 'is_published' => true],
            );
        }

        return $models;
    }

    /**
     * @param  array<string, Service>  $services
     */
    protected function testimonials(array $services): void
    {
        if (Testimonial::exists()) {
            return;
        }

        $quotes = [
            ['in-home-daytime-support', 'Sample review — replace me', 'First-time parents', 'Those first weeks would have been so much harder without Aubri. I finally got some rest, felt confident feeding, and always had someone to ask when I was unsure.'],
            ['overnight-support', 'Sample review — replace me', 'Parents of twins', 'Overnight support gave us our sanity back. Waking up rested made us better parents during the day — we can\'t recommend it enough.'],
            ['postpartum-planning', 'Sample review — replace me', 'Expecting their second', 'Planning our postpartum this time made such a difference. We had meals, help and a plan in place, and it felt so much calmer.'],
            ['in-home-daytime-support', 'Sample review — replace me', 'Postpartum client', 'Aubri walked into our home and everything felt steadier. Warm, practical and never judgemental — exactly what we needed.'],
        ];

        foreach ($quotes as $i => [$slug, $author, $context, $quote]) {
            Testimonial::create([
                'quote' => $quote,
                'author' => $author,
                'context' => $context,
                'service_id' => $services[$slug]->id ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    protected function faqs(): void
    {
        $faqs = [
            ['What is postpartum support?', '<p>Postpartum support is non-medical, in-home care for you and your family in the weeks after your baby arrives. I help with feeding, newborn care, rest, meals and household tasks, and offer emotional support and reassurance as you recover and adjust.</p>', 'General'],
            ['What does a typical visit look like?', '<p>Every visit is different and shaped around what you need that day. It might be holding baby while you nap, helping with a feed, preparing a meal, folding laundry, or talking through how you\'re feeling.</p>', 'General'],
            ['Do you help with feeding?', '<p>Yes — I support all feeding journeys, whether you\'re breastfeeding, chestfeeding, pumping, bottle feeding or a combination. If you need clinical lactation help, I\'ll connect you with a trusted local lactation consultant.</p>', 'General'],
            ['Do you provide medical care?', '<p>No. My support is non-medical. I work alongside your midwife, doctor and public health nurse, and I\'ll always encourage you to reach out to them with any medical concerns.</p>', 'General'],
            ['When should I book?', '<p>Many families book during pregnancy so support is ready when baby arrives, but you\'re always welcome to reach out after the birth too. I take a limited number of families at a time, so earlier is better.</p>', 'Booking'],
            ['What areas do you serve?', '<p>I offer in-home support across Greater Victoria and the surrounding areas. Reach out if you\'re not sure whether you\'re in my area.</p>', 'Booking'],
            ['Is postpartum support covered by insurance?', '<p>Some workplace health plans cover postpartum support. I\'m happy to provide receipts you can submit — check with your provider to see what\'s included.</p>', 'Booking'],
        ];

        foreach ($faqs as $i => [$question, $answer, $category]) {
            Faq::firstOrCreate(['question' => $question], [
                'answer' => $answer,
                'category' => $category,
                'sort_order' => $i,
            ]);
        }
    }

    protected function posts(): void
    {
        Post::firstOrCreate(['slug' => 'five-ways-to-prepare-for-the-fourth-trimester'], [
            'title' => 'Five Ways to Prepare for the Fourth Trimester',
            'excerpt' => 'Simple, practical steps you can take before baby arrives to make the early weeks at home feel calmer.',
            'body' => '<p>The weeks after birth are a time of huge change. A little preparation now can make them feel calmer and more manageable.</p><h2>1. Fill the freezer</h2><p>Batch-cook a few easy, nourishing meals — soups, stews and muffins are all one-handed friendly.</p><h2>2. Set up recovery stations</h2><p>Keep a basket of water, snacks, pads, nipple balm and burp cloths wherever you\'ll be feeding.</p><h2>3. Line up your support</h2><p>Decide who can help with meals, older children, pets and errands — and let them know how.</p><h2>4. Plan for rest</h2><p>Talk with your partner about how you\'ll share nights so everyone gets some sleep.</p><h2>5. Know your local resources</h2><p>Save the numbers for your care provider, public health nurse and a lactation consultant before you need them.</p>',
            'cover_image' => $this->stockImage('blog-mother-baby.jpg', 'blog/blog-mother-baby.jpg'),
            'published_at' => now()->subDays(3),
        ]);
    }

    /**
     * @param  array<string, Service>  $services
     */
    protected function pages(array $services): void
    {
        $daytime = $services['in-home-daytime-support']->id ?? null;
        $overnight = $services['overnight-support']->id ?? null;

        $cta = [
            'type' => 'cta_banner',
            'data' => [
                'image' => $this->stockImage('cta-cairn.jpg', 'cms/cta-cairn.jpg'),
                'heading' => 'Let\'s plan your fourth trimester',
                'text' => 'Book a free, no-pressure consultation to talk about the support your family needs.',
                'buttons' => [['label' => 'Book a Free Consult', 'url' => '/contact', 'style' => 'primary']],
            ],
        ];

        $pages = [
            'home' => [
                'title' => 'Home',
                'show_in_nav' => false,
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'image' => $this->stockImage('home-hero.jpg', 'cms/home-hero.jpg'),
                        'eyebrow' => 'Postpartum Support · Victoria, BC',
                        'heading' => 'Strong foundations for new beginnings',
                        'subheading' => 'Calm, practical, in-home care in the weeks after baby arrives — so you can rest, heal and settle into life as a family.',
                        'height' => 'large',
                        'align' => 'center',
                        'overlay' => 'dark',
                        'buttons' => [
                            ['label' => 'Book a Free Consult', 'url' => '/contact', 'style' => 'primary'],
                            ['label' => 'How I Can Help', 'url' => '/services', 'style' => 'outline'],
                        ],
                    ]],
                    ['type' => 'features', 'data' => [
                        'background' => 'white',
                        'heading' => 'Support for the Weeks After Birth',
                        'intro' => 'The fourth trimester asks a lot of new parents. You don\'t have to do it alone.',
                        'columns' => 3,
                        'items' => [
                            ['icon' => 'moon', 'title' => 'Rest & recovery', 'text' => 'Time to sleep, shower and heal while baby is cared for.'],
                            ['icon' => 'heart', 'title' => 'Feeding support', 'text' => 'Patient, judgement-free help with every way of feeding.'],
                            ['icon' => 'face-smile', 'title' => 'Newborn care', 'text' => 'Soothing, bathing, diapering and building your confidence.'],
                            ['icon' => 'chat-bubble-left-right', 'title' => 'Emotional support', 'text' => 'A listening ear for the big feelings that come with a new baby.'],
                            ['icon' => 'home', 'title' => 'Help around the home', 'text' => 'Light meals, laundry and tidying so home feels calm.'],
                            ['icon' => 'user-group', 'title' => 'The whole family', 'text' => 'Support for partners and older siblings as everyone adjusts.'],
                        ],
                    ]],
                    ['type' => 'service_cards', 'data' => [
                        'heading' => 'Ways I Can Help',
                        'intro' => 'Every family is different. Choose the support that feels right for you.',
                        'layout' => 'grid',
                        'which' => 'featured',
                        'button_label' => 'Learn More',
                    ]],
                    ['type' => 'image_text', 'data' => [
                        'background' => 'white',
                        'anchor' => 'about',
                        'image' => $this->stockImage('home-steady-presence.jpg', 'cms/home-steady-presence.jpg'),
                        'image_alt' => 'A newborn resting on a parent\'s shoulder',
                        'image_side' => 'left',
                        'eyebrow' => 'Hi, I\'m Aubri',
                        'heading' => 'A steady presence in your home',
                        'content' => '<p>I started Harbourstone because I believe every new family deserves to feel cared for — not just through pregnancy and birth, but in the weeks that follow, when the visitors go home and the real adjustment begins.</p><p>My approach is warm, practical and judgement-free. Whatever your family looks like and however you\'re feeding your baby, I\'m here to help you rest, recover and build a strong foundation for this new chapter.</p>',
                        'buttons' => [['label' => 'More About Me', 'url' => '/about', 'style' => 'outline']],
                    ]],
                    ['type' => 'steps', 'data' => [
                        'background' => 'soft',
                        'heading' => 'How It Works',
                        'intro' => 'Getting started is simple.',
                        'steps' => [
                            ['title' => 'Free consultation', 'text' => 'We meet (in person or by video) to talk about your family and the kind of support you\'re hoping for.'],
                            ['title' => 'Plan your support', 'text' => 'Together we choose daytime visits, overnights or both, and build a plan for your first weeks at home.'],
                            ['title' => 'Care at home', 'text' => 'Once baby arrives I\'m there for you — adapting each visit to what your family needs that day.'],
                        ],
                    ]],
                    ['type' => 'service_spotlight', 'data' => [
                        'service_id' => $overnight,
                        'image_side' => 'right',
                        'show_features' => true,
                        'show_testimonial' => true,
                    ]],
                    ['type' => 'testimonials', 'data' => [
                        'background' => 'accent',
                        'heading' => 'Kind Words From Families',
                        'featured_only' => true,
                        'limit' => 8,
                    ]],
                    ['type' => 'latest_posts', 'data' => [
                        'heading' => 'From the Blog',
                        'limit' => 3,
                    ]],
                    ['type' => 'contact_form', 'data' => [
                        'background' => 'white',
                        'heading' => 'Let\'s Connect',
                        'intro' => 'Have a question or ready to book a consultation? Send me a message and I\'ll be in touch soon.',
                        'show_details' => true,
                        'ask_due_date' => true,
                    ]],
                ],
            ],
            'about' => [
                'title' => 'About',
                'show_in_nav' => true,
                'nav_order' => 1,
                'meta_description' => 'Meet Aubri Porter, founder of Harbourstone Postpartum Support in Victoria, BC.',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'image' => $this->stockImage('about-hero.jpg', 'cms/about-hero.jpg'),
                        'eyebrow' => 'About',
                        'heading' => 'Meet Aubri',
                        'subheading' => 'Founder of Harbourstone Postpartum Support.',
                        'height' => 'medium',
                        'align' => 'center',
                    ]],
                    ['type' => 'image_text', 'data' => [
                        'image' => $this->stockImage('about-shoreline-cairn.jpg', 'cms/about-shoreline-cairn.jpg'),
                        'image_alt' => 'A stack of stones on a quiet shoreline',
                        'image_side' => 'left',
                        'eyebrow' => 'My story',
                        'heading' => 'Why Harbourstone?',
                        'content' => '<p>Share your story here — what drew you to postpartum work, your training, and what you love about supporting new families.</p><p>A harbour is a safe place to rest after a journey, and stones are what strong foundations are built from. Tell families what that means to you and how it shapes the care you offer.</p>',
                    ]],
                    ['type' => 'features', 'data' => [
                        'background' => 'white',
                        'heading' => 'My Approach',
                        'columns' => 3,
                        'items' => [
                            ['icon' => 'shield-check', 'title' => 'Grounded', 'text' => 'A calm, steady presence when everything feels new.'],
                            ['icon' => 'hand-raised', 'title' => 'Practical', 'text' => 'Real, hands-on help with the things that make the biggest difference.'],
                            ['icon' => 'heart', 'title' => 'Judgement-free', 'text' => 'Respect for every family, every choice and every way of feeding.'],
                        ],
                    ]],
                    ['type' => 'testimonial_quote', 'data' => ['background' => 'soft']],
                    $cta,
                ],
            ],
            'services' => [
                'title' => 'Support',
                'show_in_nav' => true,
                'nav_order' => 2,
                'meta_description' => 'In-home daytime support, overnight care and postpartum planning for new families in Greater Victoria.',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'image' => $this->stockImage('support-hero-harbour.jpg', 'cms/support-hero-harbour.jpg'),
                        'eyebrow' => 'Postpartum Support',
                        'heading' => 'Care for your fourth trimester',
                        'subheading' => 'Daytime visits, restful nights and a plan for life at home with your new baby.',
                        'height' => 'medium',
                        'align' => 'center',
                    ]],
                    ['type' => 'service_cards', 'data' => [
                        'heading' => 'Ways to work together',
                        'layout' => 'grid',
                        'which' => 'all',
                        'button_label' => 'Learn More',
                    ]],
                    ['type' => 'service_spotlight', 'data' => [
                        'background' => 'white',
                        'service_id' => $daytime,
                        'image_side' => 'left',
                        'show_features' => true,
                        'show_testimonial' => true,
                    ]],
                    ['type' => 'packages', 'data' => [
                        'heading' => 'Support Packages',
                        'intro' => 'Pre-book a block of hours and use them however you need — daytime, overnight, or a mix.',
                        'packages' => [
                            ['name' => 'Gentle Start', 'price' => '$480', 'price_note' => '12 hours of support', 'description' => 'A soft landing for your first weeks at home.', 'features' => ['Free consultation', '12 hours of in-home support', 'Text check-ins between visits', 'Local resource list'], 'button_label' => 'Get Started', 'button_url' => '/contact', 'highlighted' => false],
                            ['name' => 'Settling In', 'price' => '$900', 'price_note' => '24 hours of support', 'description' => 'Our most popular package for new families.', 'features' => ['Everything in Gentle Start', '24 hours of support', 'Postpartum planning session', 'Daytime or overnight visits'], 'button_label' => 'Get Started', 'button_url' => '/contact', 'highlighted' => true],
                            ['name' => 'Strong Foundations', 'price' => '$1,450', 'price_note' => '40 hours of support', 'description' => 'Extended care through the whole fourth trimester.', 'features' => ['Everything in Settling In', '40 hours of support', 'Priority scheduling', 'Weekly check-in calls'], 'button_label' => 'Get Started', 'button_url' => '/contact', 'highlighted' => false],
                        ],
                        'footnote' => 'Prices are placeholders — update them in the admin under Website → Pages → Support.',
                    ]],
                    ['type' => 'faq', 'data' => [
                        'background' => 'white',
                        'heading' => 'Booking Questions',
                        'category' => 'Booking',
                    ]],
                    $cta,
                ],
            ],
            'faq' => [
                'title' => 'FAQ',
                'show_in_nav' => true,
                'nav_order' => 3,
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'image' => $this->stockImage('faq-hero-tea-candle.jpg', 'cms/faq-hero-tea-candle.jpg'),
                        'eyebrow' => 'FAQ',
                        'heading' => 'Questions, answered',
                        'subheading' => 'Don\'t see your question here? I\'m always happy to chat.',
                        'height' => 'medium',
                        'align' => 'center',
                    ]],
                    ['type' => 'faq', 'data' => ['heading' => '']],
                    $cta,
                ],
            ],
            'contact' => [
                'title' => 'Contact',
                'show_in_nav' => true,
                'nav_order' => 5,
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'image' => $this->stockImage('contact-hero-tea-knit.jpg', 'cms/contact-hero-tea-knit.jpg'),
                        'eyebrow' => 'Contact',
                        'heading' => 'Let\'s connect',
                        'subheading' => 'Reach out for a free consultation or with any questions at all.',
                        'height' => 'medium',
                        'align' => 'center',
                    ]],
                    ['type' => 'contact_form', 'data' => [
                        'heading' => 'Send me a message',
                        'intro' => 'I respond to every inquiry within one business day.',
                        'show_details' => true,
                        'ask_due_date' => true,
                    ]],
                ],
            ],
        ];

        foreach ($pages as $slug => $page) {
            Page::firstOrCreate(['slug' => $slug], $page + ['is_published' => true]);
        }
    }

    /**
     * Copy a stock photo from resources/img onto the public disk and return its stored path.
     */
    protected function stockImage(string $source, string $target): ?string
    {
        $sourcePath = resource_path("img/{$source}");

        if (! is_file($sourcePath)) {
            return null;
        }

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($sourcePath));
        }

        return $target;
    }
}
