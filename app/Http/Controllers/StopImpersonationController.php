<?php

namespace App\Http\Controllers;

use App\Filament\Resources\Users\UserResource;
use App\Support\Impersonation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StopImpersonationController extends Controller
{
    /**
     * Switch back from the impersonated user to the super admin's own account.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if (! Impersonation::stop()) {
            return redirect('/admin');
        }

        $request->session()->regenerate();

        return redirect(UserResource::getUrl('index'));
    }
}
