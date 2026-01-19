<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;


class ThemeController extends Controller
{
    public function changeTheme()
    {
        $currentTheme = Cache::get('theme', 'light');
        $newTheme = $currentTheme === 'light' ? 'dark' : 'light';
        Cache::forever('theme', $newTheme);

        return response()->json(['theme' => $newTheme]);
    }
}
