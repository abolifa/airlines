<?php

namespace App\Http\Controllers;

use App\Services\Videcom\VidecomClient;
use Illuminate\Http\Request;

class VidecomGatewayController extends Controller
{
    public function command(Request $request, VidecomClient $videcom)
    {
        $secret = $request->header('X-Proxy-Secret');

        if (!$secret || !hash_equals((string)config('videcom.proxy_secret'), (string)$secret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'command' => 'required|string|max:2000',
        ]);

        $res = $videcom->runCommand($validated['command']);

        return response($res['body'] ?? ($res['fault'] ?? $res['error'] ?? ''), $res['status'] ?: 502)
            ->header('Content-Type', 'application/soap+xml; charset=utf-8')
            ->header('X-Videcom-Ok', $res['ok'] ? '1' : '0')
            ->header('X-Videcom-Endpoint', $res['endpoint']);
    }
}
