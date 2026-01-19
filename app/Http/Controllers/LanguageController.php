<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function change(Request $request): RedirectResponse
    {
        $locale = $request->lang;


        if (!in_array($locale, ['en', 'ar', 'de', 'es', 'fa', 'fr', 'hi', 'ne', 'pt', 'ru'])) {

            Log::warning("Invalid locale attempted: $locale");
            return redirect()->back()->withErrors(['locale' => 'Invalid locale']);
        }


        Cache::forever('locale', $locale);

        App::setLocale($locale);

        return redirect()->back();
    }
}
