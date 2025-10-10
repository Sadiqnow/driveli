<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use App\Models\GlobalLanguage;
use App\Models\Country;

class GlobalizationService
{
    /**
     * Get all supported languages for language picker
     */
    public function getSupportedLanguages(): array
    {
        return GlobalLanguage::active()
            ->orderBy('is_major_language', 'desc')
            ->orderBy('name')
            ->get(['code', 'name', 'native_name', 'is_major_language'])
            ->toArray();
    }

    /**
     * Get major languages only
     */
    public function getMajorLanguages(): array
    {
        return GlobalLanguage::majorLanguages()
            ->active()
            ->orderBy('name')
            ->get(['code', 'name', 'native_name'])
            ->toArray();
    }

    /**
     * Get current locale information
     */
    public function getCurrentLocaleInfo(): array
    {
        $currentLocale = App::getLocale();
        $language = GlobalLanguage::where('code', $currentLocale)->first();

        return [
            'code' => $currentLocale,
            'name' => $language?->name ?? 'Unknown',
            'native_name' => $language?->native_name ?? $language?->name ?? 'Unknown',
            'is_major' => $language?->is_major_language ?? false
        ];
    }

    /**
     * Get localized driver category names
     */
    public function getDriverCategoryNames(): array
    {
        return [
            'commercial_truck' => __('driver.categories.commercial_truck'),
            'professional' => __('driver.categories.professional'),
            'public' => __('driver.categories.public'),
            'executive' => __('driver.categories.executive'),
        ];
    }

    /**
     * Get localized employment type names
     */
    public function getEmploymentTypeNames(): array
    {
        return [
            'part_time' => __('driver.employment.part_time'),
            'full_time' => __('driver.employment.full_time'),
            'contract' => __('driver.employment.contract'),
            'assignment' => __('driver.employment.assignment'),
        ];
    }

    /**
     * Get localized status names
     */
    public function getStatusNames(): array
    {
        return [
            'active' => __('driver.status.active'),
            'inactive' => __('driver.status.inactive'),
            'suspended' => __('driver.status.suspended'),
            'blocked' => __('driver.status.blocked'),
        ];
    }

    /**
     * Get localized verification status names
     */
    public function getVerificationStatusNames(): array
    {
        return [
            'pending' => __('driver.verification.pending'),
            'verified' => __('driver.verification.verified'),
            'rejected' => __('driver.verification.rejected'),
            'reviewing' => __('driver.verification.reviewing'),
        ];
    }

    /**
     * Get localized KYC status names
     */
    public function getKycStatusNames(): array
    {
        return [
            'not_started' => __('driver.kyc.not_started'),
            'pending' => __('driver.kyc.pending'),
            'in_progress' => __('driver.kyc.in_progress'),
            'completed' => __('driver.kyc.completed'),
            'rejected' => __('driver.kyc.rejected'),
            'expired' => __('driver.kyc.expired'),
        ];
    }

    /**
     * Format currency based on country
     */
    public function formatCurrency(float $amount, string $currencyCode = null, int $countryId = null): string
    {
        if (!$currencyCode && $countryId) {
            $country = Country::find($countryId);
            $currencyCode = $country?->currency_code ?? 'USD';
        }

        $currencyCode = $currencyCode ?? 'USD';

        // Get currency symbol
        $country = Country::where('currency_code', $currencyCode)->first();
        $symbol = $country?->currency_symbol ?? $currencyCode;

        // Format based on locale
        $locale = App::getLocale();
        
        return match($locale) {
            'fr' => number_format($amount, 2, ',', ' ') . ' ' . $symbol,
            'de' => number_format($amount, 2, ',', '.') . ' ' . $symbol,
            'ar' => $symbol . ' ' . number_format($amount, 2, '.', ','),
            default => $symbol . number_format($amount, 2)
        };
    }

    /**
     * Format phone number based on country
     */
    public function formatPhoneNumber(string $phone, int $countryId = null): string
    {
        if ($countryId) {
            $country = Country::find($countryId);
            $phoneCode = $country?->phone_code ?? '+1';
            
            // Remove any existing country code
            $cleanPhone = preg_replace('/^\+?[0-9]{1,4}/', '', $phone);
            return $phoneCode . ltrim($cleanPhone, '0');
        }

        return $phone;
    }

    /**
     * Get localized date format
     */
    public function getDateFormat(): string
    {
        $locale = App::getLocale();
        
        return match($locale) {
            'en' => 'M j, Y',
            'fr' => 'd/m/Y',
            'de' => 'd.m.Y',
            'ar' => 'Y/m/d',
            default => 'Y-m-d'
        };
    }

    /**
     * Get localized time format
     */
    public function getTimeFormat(): string
    {
        $locale = App::getLocale();
        
        return match($locale) {
            'en' => 'g:i A',
            'fr' => 'H:i',
            'de' => 'H:i',
            'ar' => 'H:i',
            default => 'H:i'
        };
    }

    /**
     * Get localized datetime format
     */
    public function getDateTimeFormat(): string
    {
        return $this->getDateFormat() . ' ' . $this->getTimeFormat();
    }

    /**
     * Format date according to locale
     */
    public function formatDate($date): string
    {
        if (!$date) return '';
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($this->getDateFormat());
    }

    /**
     * Format datetime according to locale
     */
    public function formatDateTime($datetime): string
    {
        if (!$datetime) return '';
        
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return $datetime->format($this->getDateTimeFormat());
    }

    /**
     * Get countries grouped by continent for dropdowns
     */
    public function getCountriesByContinent(): array
    {
        $countries = Country::active()
            ->orderByRaw('is_supported_market DESC, priority_order ASC, name ASC')
            ->get(['id', 'name', 'continent', 'is_supported_market']);

        $grouped = [];
        foreach ($countries as $country) {
            $group = $country->is_supported_market 
                ? __('global.supported_markets') 
                : $country->continent;
            
            $grouped[$group][] = [
                'id' => $country->id,
                'name' => $country->name,
                'supported' => $country->is_supported_market
            ];
        }

        return $grouped;
    }

    /**
     * Get RTL languages list
     */
    public function getRtlLanguages(): array
    {
        return ['ar', 'he', 'fa', 'ur']; // Arabic, Hebrew, Persian, Urdu
    }

    /**
     * Check if current locale is RTL
     */
    public function isRtl(): bool
    {
        return in_array(App::getLocale(), $this->getRtlLanguages());
    }

    /**
     * Get direction for CSS (ltr/rtl)
     */
    public function getDirection(): string
    {
        return $this->isRtl() ? 'rtl' : 'ltr';
    }

    /**
     * Get layout class for RTL support
     */
    public function getLayoutClass(): string
    {
        return $this->isRtl() ? 'layout-rtl' : 'layout-ltr';
    }

    /**
     * Translate driver category with fallback
     */
    public function translateCategory(string $category): string
    {
        $translations = $this->getDriverCategoryNames();
        return $translations[$category] ?? ucwords(str_replace('_', ' ', $category));
    }

    /**
     * Translate employment type with fallback
     */
    public function translateEmploymentType(string $type): string
    {
        $translations = $this->getEmploymentTypeNames();
        return $translations[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    /**
     * Translate status with fallback
     */
    public function translateStatus(string $status): string
    {
        $translations = $this->getStatusNames();
        return $translations[$status] ?? ucfirst($status);
    }

    /**
     * Get time zones for a country
     */
    public function getTimezonesForCountry(int $countryId): array
    {
        $country = Country::find($countryId);
        
        if (!$country) {
            return ['UTC' => 'UTC'];
        }

        // For now, return the main timezone. In a full implementation,
        // you'd have a comprehensive timezone database
        return [
            $country->timezone => $country->timezone
        ];
    }
}