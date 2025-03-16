<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    public function index()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk_space' => $this->checkDiskSpace(),
            // Add more checks as needed
        ];

        $healthy = !in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks
        ], $healthy ? 200 : 503);
    }

    protected function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkCache()
    {
        try {
            Cache::put('health-check', 'ok', 10);
            return Cache::get('health-check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkDiskSpace()
    {
        $freeSpace = disk_free_space(storage_path());
        $minimumSpace = 100 * 1024 * 1024; // 100MB
        
        return $freeSpace >= $minimumSpace;
    }
}