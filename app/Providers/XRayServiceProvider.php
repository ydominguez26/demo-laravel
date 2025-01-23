<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use AsyncAws\XRay\XRayClient;
use Aws\CloudWatch\CloudWatchClient;
use AsyncAws\Core\Configuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XRayServiceProvider extends ServiceProvider
{
    protected $cloudWatch;
    protected $xray;

    public function register()
    {
        $this->app->singleton(XRayClient::class, function ($app) {
            return new XRayClient([
                'region' => env('AWS_DEFAULT_REGION'),
                'accessKeyId' => env('AWS_ACCESS_KEY_ID'),
                'accessKeySecret' => env('AWS_SECRET_ACCESS_KEY'),
                'endpoint' => 'http://' . env('AWS_XRAY_DAEMON_ADDRESS')
            ]);
        });

        $this->cloudWatch = new CloudWatchClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
    }

    public function boot()
    {
        if (env('AWS_XRAY_SDK_ENABLED', false)) {
            $this->xray = $this->app->make(XRayClient::class);
            $this->setupXRayTracing();
        }
    }

    protected function setupXRayTracing()
    {
        DB::listen(function ($query) {
            try {
                $segment = [
                    'name' => env('AWS_XRAY_NAME', 'laravel-production'),
                    'type' => 'subsegment',
                    'namespace' => 'remote',
                    'service' => 'API',
                    'start_time' => microtime(true),
                    'in_progress' => false,
                    'http' => [
                        'request' => [
                            'method' => 'SQL',
                            'url' => $query->sql
                        ]
                    ],
                    'sql' => [
                        'query' => $query->sql,
                        'bindings' => $query->bindings,
                        'execution_time' => $query->time
                    ]
                ];

                $this->xray->putTraceSegments([
                    'TraceSegmentDocuments' => [json_encode($segment)]
                ]);
                Log::info('SQL Trace successfully sent to X-Ray', [
                    'query' => $query->sql,
                    'execution_time' => $query->time
                ]);
            } catch (\Exception $e) {
                $this->logToCloudWatch('Error sending trace to X-Ray', [
                    'error' => $e->getMessage(),
                    'query' => $query->sql
                ]);
            }
        });
    }

    protected function logToCloudWatch($message, array $context = [])
    {
        try {
            $this->cloudWatch->putLogEvents([
                'logGroupName' => env('CLOUDWATCH_LOG_GROUP'),
                'logStreamName' => env('CLOUDWATCH_LOG_STREAM'),
                'logEvents' => [
                    [
                        'timestamp' => round(microtime(true) * 1000),
                        'message' => json_encode([
                            'message' => $message,
                            'context' => $context,
                            'level' => 'error',
                            'environment' => env('APP_ENV')
                        ])
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log to CloudWatch: ' . $e->getMessage());
        }
    }
}
