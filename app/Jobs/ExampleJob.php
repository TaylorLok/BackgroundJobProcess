<?php

namespace App\Jobs;

class ExampleJob
{
    private function getStoragePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/logs/';
    }

    public function process(string $param1, int $param2): void
    {
        $logPath = $this->getStoragePath() . 'example_job.log';
        
        // Ensure directory exists
        if (!is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }
        
        // Job logic here
        file_put_contents(
            $logPath,
            sprintf("[%s] Processing %s with %d\n", date('Y-m-d H:i:s'), $param1, $param2),
            FILE_APPEND
        );
    }
}
