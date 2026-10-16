<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

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
                'site_name' => 'Aubri Porter',
                'tagline' => 'Birth & Postpartum Doula',
            ],
            'theme' => [
                'preset' => 'sage',
            ],
            'topbar' => [
                'enabled' => true,
                'text' => 'Serving St. John\'s & the Avalon Peninsula — virtual support anywhere',
                'link_label' => 'Book a free consult',
                'link_url' => '/contact',
            ],
            'navigation' => [
                'items' => [
                    ['label' => 'Home', 'url' => '/', 'external' => false, 'children' => []],
                    ['label' => 'About', 'url' => '/about', 'external' => false, 'children' => []],
                    ['label' => 'Services', 'url' => '/services', 'external' => false, 'children' => [
                        ['label' => 'Birth Doula Support', 'url' => '/services/birth-doula-support'],
                        ['label' => 'Postpartum Support', 'url' => '/services/postpartum-support'],
                        ['label' => 'Childbirth Education', 'url' => '/services/childbirth-education'],
                        ['label' => 'Virtual Doula Support', 'url' => '/services/virtual-doula-support'],
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
                'phone' => '(709) 555-0123',
                'service_area' => 'St. John\'s, CBS & surrounding areas',
                'hours' => 'On call 24/7 for clients · Inquiries answered within 1 business day',
                'thank_you' => 'Thank you for reaching out! I\'ll be in touch within one business day.',
            ],
            'social' => [
                'instagram' => 'https://instagram.com/',
                'facebook' => 'https://facebook.com/',
            ],
            'footer' => [
                'about' => 'Compassionate, evidence-informed doula support for pregnancy, birth and the early weeks of parenthood.',
                'copyright' => '© {year} Aubri Porter Doula Services. All rights reserved.',
            ],
            'seo' => [
                'description' => 'Aubri Porter is a birth and postpartum doula offering calm, compassionate support for families through pregnancy, birth and the fourth trimester.',
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
                'slug' => 'birth-doula-support',
                'title' => 'Birth Doula Support',
                'tagline' => 'In-person support for your birth',
                'icon' => 'heart',
                'summary' => 'Continuous physical, emotional and informational support from late pregnancy through the first hours after birth — so you feel calm, informed and cared for.',
                'price_label' => 'Packages from $1,100',
                'button_label' => 'Book a Free Consult',
                'button_url' => '/contact',
                'features' => [
                    'Free initial consultation',
                    'Two prenatal visits',
                    'Personalized birth preferences',
                    'On call 24/7 from 37 weeks',
                    'Continuous labour support',
                    'Postpartum check-in visit',
                ],
                'description' => '<p>Whether this is your first baby or your fourth, a hospital birth, home birth or planned caesarean, you deserve to feel supported. As your birth doula I\'ll get to know you, your partner and your hopes for the day, and I\'ll be by your side from active labour until your little one is settled.</p><h2>What support looks like</h2><p>Comfort measures and positioning, calming breathing and visualization, help understanding your options, and steady reassurance for you <em>and</em> your partner. I don\'t replace your medical team — I help you feel confident working with them.</p><h2>After the birth</h2><p>I\'ll stay for the first feed and the golden hour, then visit you at home in the days that follow to talk through your birth story and answer any questions.</p>',
            ],
            [
                'slug' => 'postpartum-support',
                'title' => 'Postpartum Support',
                'tagline' => 'Care for the fourth trimester',
                'icon' => 'home',
                'summary' => 'Gentle, practical help at home in the weeks after birth — feeding support, newborn care, rest for you, and a listening ear when you need it most.',
                'price_label' => 'From $40 / hour',
                'button_label' => 'Ask About Availability',
                'button_url' => '/contact',
                'features' => [
                    'Daytime or overnight visits',
                    'Feeding & newborn care support',
                    'Light meal prep & tidying',
                    'Sibling & partner support',
                    'Emotional check-ins',
                    'Local resource referrals',
                ],
                'description' => '<p>The early weeks with a new baby are beautiful, exhausting and often overwhelming. Postpartum doula support gives you a calm, experienced person in your home so you can rest, heal and find your feet as a family.</p><p>Every family is different, so visits are flexible — a few hours a week or several visits while you recover.</p>',
            ],
            [
                'slug' => 'childbirth-education',
                'title' => 'Childbirth Education',
                'tagline' => 'Private or small-group classes',
                'icon' => 'academic-cap',
                'summary' => 'Prepare for labour with confidence. Learn what to expect, comfort techniques, and how your partner can help — in a relaxed, judgement-free setting.',
                'price_label' => '$250 per couple',
                'button_label' => 'Join the Next Class',
                'button_url' => '/contact',
                'features' => [
                    'Stages of labour explained',
                    'Comfort & coping techniques',
                    'Partner support skills',
                    'Medical options & interventions',
                    'Newborn & feeding basics',
                    'Take-home resource guide',
                ],
                'description' => '<p>Knowledge is one of the best tools for a positive birth. These classes walk you through labour and birth step by step, with lots of hands-on practice and plenty of time for questions.</p>',
            ],
            [
                'slug' => 'virtual-doula-support',
                'title' => 'Virtual Doula Support',
                'tagline' => 'Support from anywhere',
                'icon' => 'video-camera',
                'summary' => 'Not local? Video sessions, text support and birth-planning guidance wherever you are.',
                'price_label' => 'From $300',
                'button_label' => 'Learn More',
                'button_url' => '/contact',
                'features' => [
                    'Video prenatal sessions',
                    'Text & phone support',
                    'Birth preferences review',
                    'Postpartum planning',
                ],
                'description' => '<p>Virtual support is a great option if you live outside my service area, are planning a birth where in-person support isn\'t possible, or simply want guidance and reassurance through pregnancy.</p>',
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
            ['birth-doula-support', 'Sample review — replace me', 'Birth doula client', 'Having Aubri with us made all the difference. She was calm when we weren\'t, knew exactly what to say, and helped my partner feel like part of the team. We felt so supported from start to finish.'],
            ['postpartum-support', 'Sample review — replace me', 'Postpartum client', 'Those first weeks would have been so much harder without her. I finally got some sleep, felt confident feeding, and always had someone to ask when I was unsure.'],
            ['childbirth-education', 'Sample review — replace me', 'Childbirth class graduates', 'The class was relaxed, practical and actually fun. We walked into the hospital feeling prepared instead of scared.'],
            ['birth-doula-support', 'Sample review — replace me', 'Second-time parents', 'After a difficult first birth, I was nervous. Aubri helped us plan, heal and prepare — and this time it was the calm, empowering experience I had hoped for.'],
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
            ['What does a doula do?', '<p>A doula provides continuous emotional, physical and informational support before, during and after birth. I help with comfort measures, explain your options, support your partner, and help you feel calm and confident. Doulas don\'t perform medical tasks — I work alongside your doctor or midwife.</p>', 'General'],
            ['Will a doula replace my partner?', '<p>Not at all! I support your partner too — suggesting ways they can help, giving them a break when they need one, and making sure they feel included and confident.</p>', 'General'],
            ['When should I book a doula?', '<p>Many families book in the second trimester, but it\'s never too early (or usually too late) to reach out. I take a limited number of clients each month, so earlier is better.</p>', 'Booking'],
            ['Do you support hospital births, home births and caesareans?', '<p>Yes. I support all kinds of births and all kinds of families, including planned and unplanned caesareans, VBACs, and medicated or unmedicated births.</p>', 'General'],
            ['What areas do you serve?', '<p>I offer in-person support across St. John\'s and the surrounding areas, and virtual support anywhere. Reach out if you\'re not sure whether you\'re in my area.</p>', 'Booking'],
            ['Are doula services covered by insurance?', '<p>Some workplace health plans cover doula services. I\'m happy to provide receipts you can submit — check with your provider to see what\'s included.</p>', 'Booking'],
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
        Post::firstOrCreate(['slug' => 'what-to-pack-in-your-hospital-bag'], [
            'title' => 'What to Pack in Your Hospital Bag',
            'excerpt' => 'A doula\'s practical checklist for labour, recovery and bringing baby home — plus a few comforts you might not have thought of.',
            'body' => '<p>Packing your bag is one of those little milestones that makes everything feel real. Here\'s what I recommend to the families I support.</p><h2>For labour</h2><ul><li>Your birth preferences (a few printed copies)</li><li>Lip balm and hair ties</li><li>A long phone charging cable</li><li>Snacks and a refillable water bottle with a straw</li></ul><h2>For recovery</h2><ul><li>Comfortable, dark-coloured clothes</li><li>Nursing bras or soft tops</li><li>Your own pillow</li></ul><h2>For baby</h2><ul><li>A going-home outfit (and a backup)</li><li>An installed car seat</li></ul><p>Most of all, bring your support team — and remember there\'s no "perfect" bag.</p>',
            'published_at' => now()->subDays(3),
        ]);
    }

    /**
     * @param  array<string, Service>  $services
     */
    protected function pages(array $services): void
    {
        $birth = $services['birth-doula-support']->id ?? null;
        $postpartum = $services['postpartum-support']->id ?? null;

        $cta = [
            'type' => 'cta_banner',
            'data' => [
                'heading' => 'Let\'s talk about your birth',
                'text' => 'Book a free, no-pressure consultation to see if we\'re a good fit.',
                'buttons' => [['label' => 'Book a Free Consult', 'url' => '/contact', 'style' => 'primary']],
            ],
        ];

        $pages = [
            'home' => [
                'title' => 'Home',
                'show_in_nav' => false,
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'eyebrow' => 'Birth & Postpartum Doula · St. John\'s, NL',
                        'heading' => 'Calm, confident support for your birth journey',
                        'subheading' => 'Compassionate, evidence-informed care so you feel informed, steady and truly supported — from pregnancy through the fourth trimester.',
                        'height' => 'large',
                        'align' => 'center',
                        'overlay' => 'dark',
                        'buttons' => [
                            ['label' => 'Book a Free Consult', 'url' => '/contact', 'style' => 'primary'],
                            ['label' => 'Explore Services', 'url' => '/services', 'style' => 'outline'],
                        ],
                    ]],
                    ['type' => 'service_cards', 'data' => [
                        'heading' => 'How I Can Support You',
                        'intro' => 'Every family and every birth is different. Choose the support that feels right for you.',
                        'layout' => 'carousel',
                        'which' => 'featured',
                        'button_label' => 'Learn More',
                    ]],
                    ['type' => 'image_text', 'data' => [
                        'background' => 'white',
                        'anchor' => 'about',
                        'image_side' => 'left',
                        'eyebrow' => 'Hi, I\'m Aubri',
                        'heading' => 'Your birth, your way — with someone in your corner',
                        'content' => '<p>I became a doula because I believe every person deserves to feel safe, heard and respected as they bring their baby into the world.</p><p>My approach is warm, practical and judgement-free. Whatever kind of birth you\'re planning, I\'ll help you understand your options, prepare your body and mind, and feel confident making decisions that are right for your family.</p>',
                        'buttons' => [['label' => 'More About Me', 'url' => '/about', 'style' => 'outline']],
                    ]],
                    ['type' => 'service_spotlight', 'data' => [
                        'service_id' => $birth,
                        'image_side' => 'right',
                        'show_features' => true,
                        'show_testimonial' => true,
                    ]],
                    ['type' => 'steps', 'data' => [
                        'background' => 'soft',
                        'heading' => 'How It Works',
                        'intro' => 'Getting started is simple.',
                        'steps' => [
                            ['title' => 'Free consultation', 'text' => 'We meet (in person or by video) to talk about your pregnancy, your hopes and how I can help.'],
                            ['title' => 'Prepare together', 'text' => 'Prenatal visits to plan your birth preferences, practise comfort measures and answer every question.'],
                            ['title' => 'Birth & beyond', 'text' => 'I\'m on call from 37 weeks, by your side in labour, and visit afterwards to help you settle in.'],
                        ],
                    ]],
                    ['type' => 'service_spotlight', 'data' => [
                        'service_id' => $postpartum,
                        'image_side' => 'left',
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
                'meta_description' => 'Meet Aubri Porter, a birth and postpartum doula supporting families with warmth, knowledge and calm.',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'eyebrow' => 'About',
                        'heading' => 'Meet Aubri',
                        'subheading' => 'Doula, educator, and steady presence for growing families.',
                        'height' => 'medium',
                        'align' => 'center',
                    ]],
                    ['type' => 'image_text', 'data' => [
                        'image_side' => 'left',
                        'eyebrow' => 'My story',
                        'heading' => 'Why I became a doula',
                        'content' => '<p>Share your story here — what drew you to birth work, your training and certifications, and what you love about supporting families.</p><p>This is a great place to talk about your own experiences, your family, and what makes your support unique. Families choose a doula they connect with, so let your personality shine.</p>',
                    ]],
                    ['type' => 'features', 'data' => [
                        'background' => 'white',
                        'heading' => 'My Approach',
                        'columns' => 3,
                        'items' => [
                            ['icon' => 'heart', 'title' => 'Compassionate', 'text' => 'Judgement-free support for every kind of birth and every kind of family.'],
                            ['icon' => 'book-open', 'title' => 'Evidence-informed', 'text' => 'Clear, up-to-date information so you can make confident choices.'],
                            ['icon' => 'hand-raised', 'title' => 'Hands-on', 'text' => 'Comfort measures, positioning and practical help when it matters most.'],
                        ],
                    ]],
                    ['type' => 'testimonial_quote', 'data' => ['background' => 'soft']],
                    $cta,
                ],
            ],
            'services' => [
                'title' => 'Services',
                'show_in_nav' => true,
                'nav_order' => 2,
                'meta_description' => 'Birth doula support, postpartum care, childbirth education and virtual support.',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'eyebrow' => 'Services',
                        'heading' => 'Support for every stage',
                        'subheading' => 'From your first prenatal questions to the early weeks at home.',
                        'height' => 'medium',
                        'align' => 'center',
                    ]],
                    ['type' => 'service_cards', 'data' => [
                        'heading' => 'Ways to work together',
                        'layout' => 'grid',
                        'which' => 'all',
                        'button_label' => 'Learn More',
                    ]],
                    ['type' => 'packages', 'data' => [
                        'background' => 'white',
                        'heading' => 'Birth Support Packages',
                        'intro' => 'Payment plans are always available — please don\'t let cost stop you from reaching out.',
                        'packages' => [
                            ['name' => 'Essential', 'price' => '$1,100', 'description' => 'Everything you need for a supported birth.', 'features' => ['Free consultation', '2 prenatal visits', 'On call from 37 weeks', 'Continuous labour support', '1 postpartum visit'], 'button_label' => 'Get Started', 'button_url' => '/contact', 'highlighted' => false],
                            ['name' => 'Complete', 'price' => '$1,450', 'price_note' => 'or 3 payments of $485', 'description' => 'Birth support plus private childbirth education.', 'features' => ['Everything in Essential', 'Private childbirth class', 'Unlimited text support', '2 postpartum visits'], 'button_label' => 'Get Started', 'button_url' => '/contact', 'highlighted' => true],
                            ['name' => 'Fourth Trimester', 'price' => '$1,900', 'description' => 'Birth support with extended postpartum care at home.', 'features' => ['Everything in Complete', '10 hours of postpartum care', 'Feeding support', 'Postpartum planning session'], 'button_label' => 'Get Started', 'button_url' => '/contact', 'highlighted' => false],
                        ],
                        'footnote' => 'Prices are placeholders — update them in the admin under Website → Pages → Services.',
                    ]],
                    ['type' => 'faq', 'data' => [
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
}
