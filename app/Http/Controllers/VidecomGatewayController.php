<?php

namespace App\Http\Controllers;

use App\Services\Videcom\VidecomClient;
use App\Services\Videcom\VidecomSessionStore;
use Illuminate\Http\Request;

class VidecomGatewayController extends Controller
{
    public function command(Request $request, VidecomClient $videcom, VidecomSessionStore $store)
    {
        $secret = $request->header('X-Proxy-Secret');
        if (!$secret || !hash_equals((string) config('videcom.proxy_secret'), (string) $secret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'command' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:100',
        ]);

        $sessionId = $validated['session_id'] ?: $store->newSessionId();

        $res = $videcom->runCommand($validated['command']);

        $store->appendHistory(
            $sessionId,
            $validated['command'],
            (int) ($res['status'] ?? 0),
            (bool) ($res['ok'] ?? false),
            (int) config('videcom.session_history_minutes', 120)
        );

        return response(
            $res['body'] ?? ($res['fault'] ?? $res['error'] ?? ''),
            $res['status'] ?: 502
        )
            ->header('Content-Type', 'application/soap+xml; charset=utf-8')
            ->header('X-Videcom-Ok', ($res['ok'] ?? false) ? '1' : '0')
            ->header('X-Videcom-Endpoint', $res['endpoint'] ?? '')
            ->header('X-Videcom-Session', $sessionId);
    }

    public function history(Request $request, VidecomSessionStore $store)
    {
        $secret = $request->header('X-Proxy-Secret');
        if (!$secret || !hash_equals((string)config('videcom.proxy_secret'), (string)$secret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'session_id' => 'required|string|max:100',
        ]);

        return response()->json([
            'session_id' => $validated['session_id'],
            'history' => $store->getHistory($validated['session_id']),
        ]);
    }

    public function reset(Request $request, VidecomSessionStore $store)
    {
        $secret = $request->header('X-Proxy-Secret');
        if (!$secret || !hash_equals((string)config('videcom.proxy_secret'), (string)$secret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'session_id' => 'required|string|max:100',
        ]);

        $store->forget($validated['session_id']);

        return response()->json([
            'ok' => true,
            'session_id' => $validated['session_id'],
        ]);
    }
}
