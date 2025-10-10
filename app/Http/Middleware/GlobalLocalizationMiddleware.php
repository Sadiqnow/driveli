<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\GlobalLanguage;
use App\Models\Country;

class GlobalLocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->determineLocale($request);
        
        // Set the application locale
        App::setLocale($locale);
        
        // Store locale in session for persistence
        Session::put('app_locale', $locale);
        
        // Set locale for Carbon (dates)
        \Carbon\Carbon::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Determine the appropriate locale for the request
     */
    protected function determineLocale(Request $request): string
    {
        // Priority order:
        // 1. URL parameter (?lang=en)
        // 2. Session stored preference
        // 3. User's saved preference (if authenticated)
        // 4. Browser Accept-Language header
        // 5. Country default language
        // 6. Application default

        // 1. Check for URL parameter
        if ($request->has('lang')) {
            $locale = $request->input('lang');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 2. Check session
        if (Session::has('app_locale')) {
            $locale = Session::get('app_locale');
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        // 3. Check authenticated user preference
        if (auth()->check()) {
            $user = auth()->user();
            if (method_exists($user, 'getPreferredLanguage')) {
                $locale = $user->getPreferredLanguage();
                if ($this->isValidLocale($locale)) {
                    return $locale;
                }
            }
            // Check driver's communication language
            if (isset($user->preferred_communication_language)) {
                $locale = $user->preferred_communication_language;
                if ($this->isValidLocale($locale)) {
                    return $locale;
                }
            }
        }

        // 4. Check browser Accept-Language header
        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale && $this->isValidLocale($browserLocale)) {
            return $browserLocale;
        }

        // 5. Check country default (if we can determine user's country)
        $countryLocale = $this->getCountryDefaultLocale($request);
        if ($countryLocale && $this->isValidLocale($countryLocale)) {
            return $countryLocale;
        }

        // 6. Application default
        return config('app.locale', 'en');
    }

    /**
     * Check if locale is valid and supported
     */
    protected function isValidLocale(string $locale): bool
    {
        return GlobalLanguage::where('code', $locale)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get preferred locale from browser headers
     */
    protected function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,fr;q=0.8")
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $code = trim($parts[0]);
            $quality = 1.0;
            
            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = (float) substr($parts[1], 2);
            }
            
            // Extract primary language code (e.g., 'en' from 'en-US')
            $primaryCode = strtolower(substr($code, 0, 2));
            $languages[$primaryCode] = $quality;
        }

        // Sort by quality (preference)
        arsort($languages);

        // Find first supported language
        foreach (array_keys($languages) as $locale) {
            if ($this->isValidLocale($locale)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get default locale for user's country
     */
    protected function getCountryDefaultLocale(Request $request): ?string
    {
        // Try to determine country from IP (in real app, use GeoIP service)
        // For now, check if there's a country preference in session or user profile
        
        if (Session::has('user_country_id')) {
            $countryId = Session::get('user_country_id');
            $country = Country::find($countryId);
            if ($country && $country->common_languages) {
                return $country->common_languages[0] ?? null;
            }
        }

        if (auth()->check() && auth()->user()->country_id) {
            $country = Country::find(auth()->user()->country_id);
            if ($country && $country->common_languages) {
                return $country->common_languages[0] ?? null;
            }
        }

        return null;
    }
}