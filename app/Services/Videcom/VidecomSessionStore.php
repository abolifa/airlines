<?php

namespace App\Services\Videcom;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VidecomSessionStore
{
    public function newSessionId(): string
    {
        return (string)Str::uuid();
    }

    public function appendHistory(string $sessionId, string $command, int $status, bool $ok, int $minutes): void
    {
        $key = $this->historyKey($sessionId);

        $row = [
            't' => now()->toIso8601String(),
            'cmd' => $command,
            'status' => $status,
            'ok' => $ok ? 1 : 0,
        ];

        $history = Cache::get($key);
        if (!is_array($history)) $history = [];

        array_unshift($history, $row);
        $history = array_slice($history, 0, 100);

        Cache::put($key, $history, now()->addMinutes($minutes));
    }

    public function historyKey(string $sessionId): string
    {
        return "videcom:history:" . $sessionId;
    }

    public function getHistory(string $sessionId): array
    {
        $data = Cache::get($this->historyKey($sessionId));
        return is_array($data) ? $data : [];
    }

    public function forget(string $sessionId): void
    {
        Cache::forget($this->historyKey($sessionId));
    }
}
