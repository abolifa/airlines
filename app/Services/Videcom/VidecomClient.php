<?php

namespace App\Services\Videcom;

use Illuminate\Support\Facades\Http;
use Throwable;

class VidecomClient
{
    public function runCommand(string $command): array
    {
        $token = (string) config('videcom.token');
        $endpoint = rtrim((string) config('videcom.base_url'), '/') . '?op=RunVRSCommand';

        $tokenEsc = htmlspecialchars($token, ENT_XML1);
        $cmdEsc   = htmlspecialchars($command, ENT_XML1);

        $xml = <<<XML
<soap12:Envelope xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <msg xmlns="http://videcom.com/">
      <Token>{$tokenEsc}</Token>
      <Command>{$cmdEsc}</Command>
    </msg>
  </soap12:Body>
</soap12:Envelope>
XML;

        try {
            $res = Http::withOptions([
                'version' => 1.1,
            ])
                ->withHeaders([
                    'Accept' => 'application/soap+xml, text/xml',
                    'Content-Type' => 'application/soap+xml; charset=utf-8',
                    'SOAPAction' => 'http://videcom.com/RunVRSCommand',
                    'Accept-Encoding' => 'identity',
                ])
                ->connectTimeout(10)
                ->timeout(30)
                ->withBody($xml, 'application/soap+xml; charset=utf-8')
                ->post($endpoint);

            return [
                'ok' => $res->successful(),
                'status' => $res->status(),
                'endpoint' => $endpoint,
                'body' => $res->body(),
                'fault' => null,
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'body' => null,
                'fault' => null,
            ];
        }
    }
}
