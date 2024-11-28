<?php 

use Symfony\Component\Process\Process;

if (!function_exists('runBackgroundJob')) {
    function runBackgroundJob(string $class, string $method, array $params = []): void
    {
        $script = base_path('job_runner.php');
        $paramsJson = json_encode($params);

        // Build the command with cross-platform support
        $command = PHP_OS_FAMILY === 'Windows'
            ? "php \"{$script}\" --class=\"{$class}\" --method=\"{$method}\" --params=\"{$paramsJson}\""
            : "php {$script} --class='{$class}' --method='{$method}' --params='{$paramsJson}' > /dev/null 2>&1 &";

        // Run the process
        Process::fromShellCommandline($command)->start();
    }
}
