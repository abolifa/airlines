<?php

namespace App\Services\Videcom;

use Illuminate\Support\Facades\Http;
use Throwable;

class VidecomClient
{
    public function runCommand(string $command): array
    {
        $token = (string)config('videcom.token');
        $endpoint = $this->endpoint();
        $xml = $this->buildSoap12MsgEnvelope($token, $command);

        try {
            $res = Http::withHeaders([
                'Accept' => 'application/soap+xml, text/xml',
                'Content-Type' => 'application/soap+xml; charset=utf-8; action="http://videcom.com/RunVRSCommand"',
            ])
                ->timeout((int)config('videcom.timeout', 60))
                ->withBody($xml, 'application/soap+xml; charset=utf-8')
                ->post($endpoint);
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

        $body = $res->body();

        return [
            'ok' => $res->successful(),
            'status' => $res->status(),
            'endpoint' => $endpoint,
            'body' => $body,
            'fault' => $this->extractSoapFault($body),
        ];
    }

    public function endpoint(): string
    {
        $base = rtrim((string)config('videcom.base_url'), '/');
        return $base . '?op=RunVRSCommand';
    }

    private function buildSoap12MsgEnvelope(string $token, string $command): string
    {
        $tokenEsc = htmlspecialchars($token, ENT_XML1);
        $cmdEsc = htmlspecialchars($command, ENT_XML1);

        return <<<XML
<soap12:Envelope xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <msg xmlns="http://videcom.com/">
      <Token>$tokenEsc</Token>
      <Command>$cmdEsc</Command>
    </msg>
  </soap12:Body>
</soap12:Envelope>
XML;
    }

    private function extractSoapFault(?string $xml): ?string
    {
        if (!$xml) return null;

        try {
            $clean = preg_replace('/xmlns(:\w+)?="[^"]*"/i', '', $xml);
            $sxml = simplexml_load_string($clean);
            if (!$sxml) return null;

            $fault = $sxml->Body->Fault ?? null;
            if (!$fault) return null;

            $reason = (string)($fault->Reason->Text ?? '');
            if ($reason) return trim($reason);

            $faultString = (string)($fault->faultstring ?? '');
            return $faultString ? trim($faultString) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
