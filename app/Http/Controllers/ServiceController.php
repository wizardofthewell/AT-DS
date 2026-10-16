<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    public function show(Service $service)
    {
        abort_unless($service->is_published, 404);

        $testimonials = $service->testimonials()->published()->ordered()->get();

        $others = Service::published()
            ->ordered()
            ->whereKeyNot($service->getKey())
            ->limit(3)
            ->get();

        return view('services.show', compact('service', 'testimonials', 'others'));
    }
}
