<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AsteroidController extends Controller
{
    public function index(Request $request)
    {
        // Jika user mengirim tanggal baru lewat form, simpan ke dalam Session
        if ($request->filled('date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->input('date'))) {
            $selectedDate = $request->input('date');
            $request->session()->put('last_scan_date', $selectedDate);
        } else {
            // Jika tidak ada parameter tanggal di URL, ambil dari Session (default hari ini)
            $selectedDate = $request->session()->get('last_scan_date', date('Y-m-d'));
        }

        // Prioritas API key: input form -> session (yang pernah diketik user) -> .env via config -> DEMO_KEY.
        // Key TIDAK lagi ditulis langsung di kode.
        if ($request->filled('api_key')) {
            $request->session()->put('nasa_api_key', trim($request->input('api_key')));
        }
        if ($request->boolean('reset_key')) {
            $request->session()->forget('nasa_api_key');
        }
        $userKey = $request->session()->get('nasa_api_key');
        $apiKey  = $userKey ?: (config('services.nasa.key') ?: 'DEMO_KEY');

        // Hitung jumlah aktivitas scan pada Session saat ini
        $scanCount = $request->session()->get('scan_count', 0) + 1;
        $request->session()->put('scan_count', $scanCount);

        $asteroids = [];
        $isLive = false;
        $apiError = null;

        try {
            $response = Http::timeout(8)->get('https://api.nasa.gov/neo/rest/v1/feed', [
                'start_date' => $selectedDate,
                'end_date'   => $selectedDate,
                'api_key'    => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $neoList = $data['near_earth_objects'][$selectedDate] ?? [];

                foreach ($neoList as $item) {
                    $approach = $item['close_approach_data'][0] ?? [];

                    $asteroids[] = [
                        'id'             => (string) ($item['id'] ?? uniqid()),
                        'name'           => $item['name'] ?? 'Unknown NEO',
                        'diameter_m'     => (int) round($item['estimated_diameter']['meters']['estimated_diameter_max'] ?? 0),
                        'distance_km'    => (int) round((float) ($approach['miss_distance']['kilometers'] ?? 0)),
                        'lunar_distance' => round((float) ($approach['miss_distance']['lunar'] ?? 0), 2),
                        'velocity_kmh'   => (int) round((float) ($approach['relative_velocity']['kilometers_per_hour'] ?? 0)),
                        'is_hazardous'   => (bool) ($item['is_potentially_hazardous_asteroid'] ?? false),
                        'description'    => 'Telemetry fetched via Laravel Backend from NASA NeoWS for ' . $selectedDate . '.',
                    ];
                }

                if (count($asteroids) > 0) {
                    $isLive = true;
                } else {
                    $apiError = 'NASA tidak mengembalikan data asteroid untuk tanggal ' . $selectedDate . '. Menampilkan JPL Simulator.';
                }
            } elseif (in_array($response->status(), [429, 403], true)) {
                $apiError = $apiKey === 'DEMO_KEY'
                    ? 'Batas request DEMO_KEY tercapai. Masukkan API key pribadi pada kolom API KEY di bawah, lalu klik CONNECT.'
                    : 'API key ditolak / batas request tercapai (HTTP ' . $response->status() . '). Periksa key kamu lalu coba lagi.';
            } else {
                $apiError = 'NASA API membalas error HTTP ' . $response->status() . '. Menampilkan JPL Simulator.';
            }
        } catch (\Exception $e) {
            $apiError = 'Tidak bisa terhubung ke NASA (cek koneksi internet). Menampilkan JPL Simulator.';
        }

        // Fallback data simulasi sesuai struktur ASTRONET // NEO RADAR (jika data live kosong)
        if (!$isLive) {
            $asteroids = [
                [
                    'id' => '3542519',
                    'name' => '(2024 YR4)',
                    'diameter_m' => 62,
                    'distance_km' => 1840000,
                    'lunar_distance' => 4.79,
                    'velocity_kmh' => 48920,
                    'is_hazardous' => true,
                    'description' => 'Recently detected near-Earth asteroid with high orbital uncertainty. Requires precise telescopic tracking for trajectory refinement.'
                ],
                [
                    'id' => '20099942',
                    'name' => '99942 Apophis (2004 MN4)',
                    'diameter_m' => 370,
                    'distance_km' => 31200000,
                    'lunar_distance' => 81.25,
                    'velocity_kmh' => 107280,
                    'is_hazardous' => true,
                    'description' => 'Famous Potentially Hazardous Asteroid. Will make a historic close approach to Earth in April 2029 within satellite orbit altitude.'
                ],
                [
                    'id' => '2101955',
                    'name' => '101955 Bennu (1999 RQ36)',
                    'diameter_m' => 492,
                    'distance_km' => 14200000,
                    'lunar_distance' => 36.97,
                    'velocity_kmh' => 101520,
                    'is_hazardous' => true,
                    'description' => "Carbonaceous asteroid visited by NASA's OSIRIS-REx spacecraft, which returned pristine surface samples back to Earth."
                ],
                [
                    'id' => '20004179',
                    'name' => '4179 Toutatis (1989 AC)',
                    'diameter_m' => 2450,
                    'distance_km' => 28900000,
                    'lunar_distance' => 75.26,
                    'velocity_kmh' => 122040,
                    'is_hazardous' => true,
                    'description' => 'Elongated, chaotic rotator tumbling through space with an eccentric orbit inclined close to the ecliptic plane.'
                ],
                [
                    'id' => '20000433',
                    'name' => '433 Eros (1898 DQ)',
                    'diameter_m' => 16840,
                    'distance_km' => 22400000,
                    'lunar_distance' => 58.33,
                    'velocity_kmh' => 87480,
                    'is_hazardous' => false,
                    'description' => 'The second-largest Near-Earth Object. Historic target of the NEAR Shoemaker probe which landed on its surface in 2001.'
                ],
                [
                    'id' => '20003122',
                    'name' => '3122 Florence (1981 ET3)',
                    'diameter_m' => 4500,
                    'distance_km' => 7000000,
                    'lunar_distance' => 18.23,
                    'velocity_kmh' => 55296,
                    'is_hazardous' => true,
                    'description' => 'Large triple-asteroid system featuring two small orbiting moons discovered during radar observations in 2017.'
                ],
                [
                    'id' => '3843821',
                    'name' => '(2023 DW)',
                    'diameter_m' => 50,
                    'distance_km' => 9400000,
                    'lunar_distance' => 24.47,
                    'velocity_kmh' => 88740,
                    'is_hazardous' => false,
                    'description' => 'Small Apollo-class NEO monitored for future potential approaches; currently classified as low-threat.'
                ],
                [
                    'id' => '20001620',
                    'name' => '1620 Geographos (1951 RA)',
                    'diameter_m' => 2500,
                    'distance_km' => 19800000,
                    'lunar_distance' => 51.56,
                    'velocity_kmh' => 114120,
                    'is_hazardous' => true,
                    'description' => 'Highly elongated asteroid with a length roughly 2.5 times its width, creating distinct light curve signatures.'
                ],
            ];
        }

        // Mengembalikan Response berupa view 'welcome' beserta data dari Controller
        return view('welcome', [
            'asteroids'    => $asteroids,
            'selectedDate' => $selectedDate,
            'isLive'       => $isLive,
            'scanCount'    => $scanCount,
            'apiError'     => $apiError,
            'userApiKey'   => $userKey,
        ]);
    }
}