<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

Route::get('/health', function () {
    $health = [
        'status' => 'healthy',
        'components' => [
            'database' => 'ok',
            'cache' => 'ok'
        ]
    ];
    $status_code = 200;

    // Cek Database
    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $health['status'] = 'unhealthy';
        $health['components']['database'] = 'unreachable - ' . $e->getMessage();
        $status_code = 503;
    }

    // Cek Cache
    try {
        Cache::put('health_check', true, 10);
        if (!Cache::get('health_check')) {
            throw new \Exception('Cache get failed');
        }
    } catch (\Exception $e) {
        $health['status'] = 'unhealthy';
        $health['components']['cache'] = 'unreachable - ' . $e->getMessage();
        $status_code = 503;
    }

    return response()->json($health, $status_code);
});
