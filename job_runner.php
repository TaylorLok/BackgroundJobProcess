<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/vendor/autoload.php';

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        // Define your storage path here (relative to the project root)
        return __DIR__ . '/storage/' . $path;
    }
}

// Logger setup
$log = new Logger('background_jobs');
$log->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/background_jobs.log', Logger::INFO));
$errorLog = new Logger('background_jobs_errors');
$errorLog->pushHandler(new StreamHandler(__DIR__ . '/storage/logs/background_jobs_errors.log', Logger::ERROR));

// Configurations
$maxRetries = 3;
$retryDelay = 5;

try {
    // Fetch input arguments
    $input = getopt('', ['class:', 'method:', 'params:', 'retries::', 'delay::', 'priority::']);

    if (!isset($input['class'], $input['method'])) {
        throw new Exception("Class and method are required.");
    }

    // Sanitize and validate inputs
    $class = filter_var($input['class'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $method = filter_var($input['method'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $params = isset($input['params']) ? json_decode($input['params'], true) : [];
    $retries = $options['retries'] ?? 1;  
    $delay = $options['delay'] ?? 0;      
    $priority = $options['priority'] ?? 0; 

    if (!is_array($params)) {
        throw new Exception("Invalid parameters: Must be a valid JSON string.");
    }

    // Allowed classes for security
    $allowedClasses = [
        \App\Jobs\ExampleJob::class,
        // You can add more job classes in this list as needed
    ];
    
    if (!in_array($class, $allowedClasses)) {
        throw new Exception("Unauthorized class: {$class}");
    }

    // Check if the class exists
    if (!class_exists($class)) {
        throw new Exception("Class {$class} not found.");
    }

    // Instantiate the class
    $instance = new $class();

    // Check if the method exists
    if (!method_exists($instance, $method)) {
        throw new Exception("Method {$method} does not exist in class {$class}.");
    }

    // Get method reflection to handle parameters dynamically
    $reflectionMethod = new ReflectionMethod($class, $method);
    $methodParameters = $reflectionMethod->getParameters();

    // Ensure the number of parameters matches
    if (count($params) !== count($methodParameters)) {
        throw new Exception("Method {$method} expects " . count($methodParameters) . " parameters, " . count($params) . " given.");
    }

    // Cast parameters to the correct type dynamically
    foreach ($methodParameters as $index => $param) {
        $paramType = $param->getType();
        if ($paramType) {
            if ($paramType->getName() === 'int') {
                $params[$index] = (int) $params[$index];
            } elseif ($paramType->getName() === 'string') {
                $params[$index] = (string) $params[$index];
            }
        }
    }

    // Priority-based job handling
    if ($priority > 0) {
        // Higher priority means we execute it sooner
        $log->info("High priority job, executing immediately.", [
            'class' => $class,
            'method' => $method,
            'priority' => $priority,
            'params' => $params,
        ]);
    } else {
        // If it's a low priority job, we simulate a delay (for testing purposes)
        $log->info("Low priority job, will be delayed.", [
            'class' => $class,
            'method' => $method,
            'priority' => $priority,
            'params' => $params,
        ]);
        // Simulate delay for low priority jobs
        sleep(5);
    }

    // Execute the job with retries
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Call the method dynamically with parameters
            call_user_func_array([$instance, $method], $params);

            // Log success
            $log->info("Job executed successfully", [
                'class' => $class,
                'method' => $method,
                'params' => $params,
                'attempt' => $attempt,
            ]);
            exit(0); // Exit with success
        } 
        catch (Exception $e) {
            // Log error on failure
            $errorLog->error("Attempt {$attempt}: " . $e->getMessage(), [
                'class' => $class,
                'method' => $method,
                'params' => $params,
            ]);

            if ($attempt === $maxRetries) {
                throw new Exception("Job failed after {$maxRetries} attempts.");
            }

            // Retry after a delay
            sleep($retryDelay);
        }
    }
} 
catch (Exception $e) {
    // Log final failure
    $errorLog->error($e->getMessage(), [
        'class' => $input['class'] ?? null,
        'method' => $input['method'] ?? null,
        'params' => $input['params'] ?? null,
    ]);
    exit(1); // Exit with failure
}
