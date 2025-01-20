<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use AWS;

class XRayMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $xrayClient = new \Aws\XRay\XRayClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);

        // Inicia el segmento
        $traceHeader = $request->header('X-Amzn-Trace-Id');
        $name = env('APP_NAME', 'Laravel') . '-' . $request->path();
        
        $segment = [
            'name' => $name,
            'trace_id' => $traceHeader ?? \Aws\XRay\IdGenerator::generateTraceId(),
            'start_time' => microtime(true),
            'service' => [
                'version' => '1.0'
            ]
        ];

        // Ejecuta la request
        $response = $next($request);

        // Finaliza el segmento
        $segment['end_time'] = microtime(true);
        $segment['http'] = [
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'client_ip' => $request->ip()
            ],
            'response' => [
                'status' => $response->status(),
                'content_length' => $response->headers->get('Content-Length')
            ]
        ];

        // Envía el segmento a X-Ray
        try {
            $xrayClient->putTraceSegments([
                'TraceSegmentDocuments' => [json_encode($segment)]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending trace to X-Ray: ' . $e->getMessage());
        }

        return $response;
    }
}