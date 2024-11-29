<?php

namespace App\Jobs;

use App\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $param1;
    private $param2;
    private $retries;
    private $executionDelay;
    private $priority;

    // Add max tries property
    public $tries = 3;
    public $timeout = 120;

    public function __construct($param1, $param2, $retries, $executionDelay, $priority)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->retries = $retries;
        $this->executionDelay = $executionDelay;
        $this->priority = $priority;
    }

    public function handle(): void
    {
        try {
            // Call the helper function to run the background job
            runBackgroundJob(
                self::class, 
                'process', 
                [$this->param1, $this->param2], 
                $this->retries, 
                $this->executionDelay, 
                $this->priority
            );

            Log::info('TestJob dispatched to background', [
                'param1' => $this->param1,
                'param2' => $this->param2,
            ]);
        } catch (\Throwable $e) {
            Log::error('TestJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getStoragePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/logs/';
    }

    public function process(string $param1, int $param2): void
    {
        try {
            $logPath = $this->getStoragePath() . 'example_job.log';
            
            if (!is_dir(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }
            
            file_put_contents(
                $logPath,
                sprintf("[%s] Processing %s with %d\n", date('Y-m-d H:i:s'), $param1, $param2),
                FILE_APPEND
            );

            Log::info('TestJob process completed', [
                'param1' => $param1,
                'param2' => $param2,
            ]);
        } catch (\Throwable $e) {
            Log::error('TestJob process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('TestJob failed with exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
