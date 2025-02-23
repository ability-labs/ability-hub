<?php

namespace App\Http\Controllers;

use App\Enums\AppLocale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class LanguageSelectorController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $locales = collect(AppLocale::cases())
            ->map(fn(\UnitEnum $lang) => $lang->name)
            ->toArray();

        $attributes = $request->validate([
            'locale' => [ Rule::in($locales) ]
        ]);

        session(['locale' => $attributes['locale']]);

        if ($request->ajax())
            return response()->json([
                'success' => true
            ]);
        else
            return redirect()->back()->with([
                'language-switched' => $attributes['locale']
            ]);

    }
}
