<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GscController extends Controller
{
    private string $clientId     = '547500034491-04taolgl1ubm8lf0uen7g80cl4o5ijjv.apps.googleusercontent.com';
    private string $clientSecret = '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';
    private string $redirectUri  = 'https://www.servicebazzar.com/admin/gsc/callback';
    private string $siteUrl      = 'https://www.servicebazzar.com/';

    public function index()
    {
        $token = $this->getAccessToken();
        $data  = [];

        if ($token) {
            $data['performance'] = $this->fetchPerformance($token);
            $data['topPages']    = $this->fetchTopPages($token);
            $data['topQueries']  = $this->fetchTopQueries($token);
        }

        return view('admin.gsc.index', [
            'connected'   => (bool) $token,
            'authUrl'     => $token ? null : $this->buildAuthUrl(),
            'performance' => $data['performance'] ?? null,
            'topPages'    => $data['topPages']    ?? [],
            'topQueries'  => $data['topQueries']  ?? [],
        ]);
    }

    public function auth()
    {
        return redirect($this->buildAuthUrl());
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('admin.gsc.index')->with('error', 'Authorization failed.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $request->input('code'),
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        if (!$response->successful()) {
            return redirect()->route('admin.gsc.index')->with('error', 'Token exchange failed.');
        }

        $tokens = $response->json();

        // Store refresh token permanently
        if (!empty($tokens['refresh_token'])) {
            $this->saveRefreshToken($tokens['refresh_token']);
        }

        // Store access token in cache for 50 min
        cache()->put('gsc_access_token', $tokens['access_token'], now()->addMinutes(50));

        return redirect()->route('admin.gsc.index')->with('success', 'Google Search Console connected!');
    }

    public function disconnect()
    {
        cache()->forget('gsc_access_token');
        $this->saveRefreshToken('');
        return redirect()->route('admin.gsc.index')->with('success', 'Disconnected.');
    }

    private function buildAuthUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/webmasters.readonly',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);
    }

    private function getAccessToken(): ?string
    {
        // Try cached access token first
        if ($token = cache()->get('gsc_access_token')) {
            return $token;
        }

        // Try refreshing with refresh token
        $refreshToken = $this->loadRefreshToken();
        if (!$refreshToken) {
            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $token = $response->json('access_token');
        cache()->put('gsc_access_token', $token, now()->addMinutes(50));
        return $token;
    }

    private function fetchPerformance(string $token): array
    {
        $response = Http::withToken($token)->post(
            "https://searchconsole.googleapis.com/webmasters/v3/sites/" . urlencode($this->siteUrl) . "/searchAnalytics/query",
            [
                'startDate'  => now()->subDays(28)->toDateString(),
                'endDate'    => now()->subDays(1)->toDateString(),
                'dimensions' => ['date'],
                'rowLimit'   => 28,
            ]
        );

        if (!$response->successful()) {
            return [];
        }

        $rows   = $response->json('rows') ?? [];
        $clicks = array_sum(array_column($rows, 'clicks'));
        $impr   = array_sum(array_column($rows, 'impressions'));
        $pos    = count($rows) ? array_sum(array_column($rows, 'position')) / count($rows) : 0;
        $ctr    = $impr > 0 ? ($clicks / $impr) * 100 : 0;

        return [
            'clicks'      => (int) $clicks,
            'impressions' => (int) $impr,
            'ctr'         => round($ctr, 2),
            'position'    => round($pos, 1),
            'rows'        => $rows,
        ];
    }

    private function fetchTopPages(string $token): array
    {
        $response = Http::withToken($token)->post(
            "https://searchconsole.googleapis.com/webmasters/v3/sites/" . urlencode($this->siteUrl) . "/searchAnalytics/query",
            [
                'startDate'  => now()->subDays(28)->toDateString(),
                'endDate'    => now()->subDays(1)->toDateString(),
                'dimensions' => ['page'],
                'rowLimit'   => 10,
                'orderBy'    => [['fieldName' => 'clicks', 'sortOrder' => 'DESCENDING']],
            ]
        );

        return $response->successful() ? ($response->json('rows') ?? []) : [];
    }

    private function fetchTopQueries(string $token): array
    {
        $response = Http::withToken($token)->post(
            "https://searchconsole.googleapis.com/webmasters/v3/sites/" . urlencode($this->siteUrl) . "/searchAnalytics/query",
            [
                'startDate'  => now()->subDays(28)->toDateString(),
                'endDate'    => now()->subDays(1)->toDateString(),
                'dimensions' => ['query'],
                'rowLimit'   => 10,
                'orderBy'    => [['fieldName' => 'clicks', 'sortOrder' => 'DESCENDING']],
            ]
        );

        return $response->successful() ? ($response->json('rows') ?? []) : [];
    }

    private function saveRefreshToken(string $token): void
    {
        file_put_contents(
            storage_path('app/private/gsc-refresh-token.txt'),
            $token
        );
    }

    private function loadRefreshToken(): ?string
    {
        $path = storage_path('app/private/gsc-refresh-token.txt');
        if (!file_exists($path)) {
            return null;
        }
        $token = trim(file_get_contents($path));
        return $token ?: null;
    }
}
