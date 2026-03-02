<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    private $settingsFile = 'settings.json';
    private $apiKeysFile = 'api_keys.json';

    /**
     * Get settings
     */
    public function index()
    {
        $settings = $this->getSettings();
        $settings['apiKeys'] = $this->getApiKeys();
        $settings['seo'] = $this->getSeo();
        return response()->json($settings);
    }


    /**
     * Save settings
     */
    public function store(Request $request)
    {
        $existingSettings = $this->getSettings();

        $settings = [
            'email' => $request->input('email', ''),
            'phone' => $request->input('phone', ''),
            'address' => $request->input('address', ''),
            'facebook' => $request->input('facebook', ''),
            'zalo' => $request->input('zalo', ''),
            'siteName' => $request->input('siteName', 'SànĐất'),
            'logo' => $request->input('logo', $existingSettings['logo'] ?? ''),
            'favicon' => $request->input('favicon', $existingSettings['favicon'] ?? ''),
            'show_bct_badge' => (bool) $request->input('show_bct_badge', $existingSettings['show_bct_badge'] ?? false),
            'bct_image' => $request->input('bct_image', $existingSettings['bct_image'] ?? ''),
        ];

        Storage::put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['message' => 'Settings saved successfully', 'data' => $settings]);
    }

    /**
     * Save API keys
     */
    public function storeApiKeys(Request $request)
    {
        $apiKeys = [
            'googleMapsKey' => $request->input('googleMapsKey', ''),
            'facebookAppId' => $request->input('facebookAppId', ''),
            'facebookAppSecret' => $request->input('facebookAppSecret', ''),
            'recaptchaSiteKey' => $request->input('recaptchaSiteKey', ''),
        ];

        Storage::put($this->apiKeysFile, json_encode($apiKeys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['message' => 'API Keys saved successfully', 'data' => $apiKeys]);
    }

    /**
     * Upload logo or favicon
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048',
            'type' => 'required|in:logo,favicon,bct_image'
        ]);

        $type = $request->input('type');
        $file = $request->file('file');

        $extension = $file->getClientOriginalExtension();
        $filename = $type . '_' . time() . '.' . $extension;

        $path = $file->storeAs('public/settings', $filename);
        $url = '/storage/settings/' . $filename;

        $settings = $this->getSettings();
        $settings[$type] = $url;
        Storage::put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'message' => ucfirst($type) . ' uploaded successfully',
            'url' => $url,
            'path' => $path
        ]);
    }

    /**
     * Get settings from file
     */
    public function getSettings()
    {
        if (Storage::exists($this->settingsFile)) {
            $content = Storage::get($this->settingsFile);
            return json_decode($content, true) ?? $this->getDefaultSettings();
        }
        return $this->getDefaultSettings();
    }

    /**
     * Get API keys from file
     */
    private function getApiKeys()
    {
        if (Storage::exists($this->apiKeysFile)) {
            $content = Storage::get($this->apiKeysFile);
            return json_decode($content, true) ?? [];
        }
        return [];
    }

    /**
     * Default settings
     */
    private function getDefaultSettings()
    {
        return [
            'email' => 'contact@sandat.vn',
            'phone' => '0123 456 789',
            'address' => 'TP. Hồ Chí Minh',
            'facebook' => '',
            'zalo' => '',
            'siteName' => 'SànĐất',
            'logo' => '',
            'favicon' => '',
        ];
    }

    /**
     * Save SEO settings
     */
    public function storeSeo(Request $request)
    {
        $seo = [
            'metaTitle' => $request->input('metaTitle', ''),
            'metaDescription' => $request->input('metaDescription', ''),
            'metaKeywords' => $request->input('metaKeywords', ''),
            'canonicalUrl' => $request->input('canonicalUrl', ''),
            'ogTitle' => $request->input('ogTitle', ''),
            'ogDescription' => $request->input('ogDescription', ''),
            'ogImage' => $request->input('ogImage', ''),
            'twitterTitle' => $request->input('twitterTitle', ''),
            'twitterDescription' => $request->input('twitterDescription', ''),
            'schemaOrgName' => $request->input('schemaOrgName', ''),
            'schemaOrgLogo' => $request->input('schemaOrgLogo', ''),
            'schemaCustom' => $request->input('schemaCustom', ''),
            'robotsMeta' => $request->input('robotsMeta', 'index, follow'),
            'sitemapUrl' => $request->input('sitemapUrl', ''),
            'googleVerification' => $request->input('googleVerification', ''),
        ];

        Storage::put('seo.json', json_encode($seo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['message' => 'SEO settings saved successfully', 'data' => $seo]);
    }

    /**
     * Get SEO settings from file
     */
    private function getSeo()
    {
        if (Storage::exists('seo.json')) {
            $content = Storage::get('seo.json');
            return json_decode($content, true) ?? [];
        }
        return [];
    }
}

