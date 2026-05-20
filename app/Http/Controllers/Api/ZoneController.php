<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;

class ZoneController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = Zone::orderBy('sort')->get(['id', 'name', 'slug']);
        return response()->json(['zones' => $zones]);
    }
}
