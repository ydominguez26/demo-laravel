<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use AsyncAws\XRay\XRayClient;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class XRayMiddleware
{
    protected $xray;

    public function __construct(XRayClient $xray)
    {
        $this->xray = $xray;
    }

    public function handle(Request $request, Closure $next)
    {
        Log::debug('Middleware X-Ray ejecutado');

        $traceId = str_replace('-', '', Uuid::uuid4()->toString());
        $segmentId = str_replace('-', '', Uuid::uuid4()->toString());

        $segment = [
            'name' => env('AWS_XRAY_NAME', 'laravel-production'),
            'trace_id' => $traceId,
            'id' => $segmentId,
            'start_time' => microtime(true),
            'service' => [
                'version' => '1.0'
            ],
            'aws' => [
                'xray' => [
                    'sdk_version' => 'Laravel ' . app()->version(),
                    'sdk' => 'X-Ray for Laravel'
                ]
            ],
            'http' => [
                'request' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'user_agent' => $request->userAgent(),
                    'client_ip' => $request->ip(),
                ],
            ],
        ];

        $response = $next($request);

        $segment['end_time'] = microtime(true);
        $segment['http']['response'] = [
            'status' => $response->status(),
            'content_length' => strlen($response->content()),
        ];

        Log::debug('Segment to send: ' . json_encode($segment));

        try {
            $this->xray->putTraceSegments([
                'TraceSegmentDocuments' => [json_encode($segment)]
            ]);
            Log::info('Trace successfully sent to X-Ray', [
                'trace_id' => $traceId,
                'segment_id' => $segmentId,
                'xray_name' => env('AWS_XRAY_NAME')
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending trace to X-Ray', [
                'error' => $e->getMessage(),
                'daemon_address' => env('AWS_XRAY_DAEMON_ADDRESS'),
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials_key' => env('AWS_ACCESS_KEY_ID') ? 'Presente' : 'Ausente'
            ]);
        }

        return $response;
    }
}
