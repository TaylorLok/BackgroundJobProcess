# Background Job Documentation

## Introduction

The `runBackgroundJob` A lightweight, standalone background job processing system that operates independently of Laravel's queue system. This implementation allows you to execute PHP classes as background jobs without relying on Laravel's built-in queue infrastructure.

## Clone and Install the Application

Follow the steps below to clone the project, install the dependencies, and run the application:

### Step 1: Clone the Repository

First, clone the repository to your local machine:

```bash
git clone https://github.com/TaylorLok/BackgroundJobProcess.git
```

### Step 2: Install Dependencies

```bash
cd BackgroundJobProcess
composer install
npm install
npm run dev
```

### Step 3: Set Up Environment File

```bash
cp .env.example .env
```

```bash
php artisan key:generate
```

```bash
php artisan migrate
```

### Step 4: Run the Application

```bash
php artisan serve
```

## Usage

To use the `runBackgroundJob` function, you need to define a class that will be executed as a background job. Although this system operates independently of Laravel's queue system, it still follows a job class structure, similar to how jobs are defined in Laravel's queue system.

Here's an example of a job class:

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExampleJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        // The code you want to run in the background goes here
    }
}
```

To run this job in the background, you can use the `runBackgroundJob` function like this:

```php
runBackgroundJob(ExampleJob::class);
```

This will add the job to the queue and it will be processed in the background.

## Configuration

The `runBackgroundJob` function offers several configuration options to control how your jobs are processed. You can customize job retry attempts, delay, and priority.

### Retry Attempts

By default, a job will be retried once if it fails. You can modify the number of retry attempts by passing an additional argument to the `runBackgroundJob` function:

```php
runBackgroundJob(ExampleJob::class, 3);
```

This will make the job retry up to 3 times if it fails.

### Delay

You can configure a delay before the job is processed. This can be useful if you want to give other tasks a chance to finish before the job starts. You can set a delay by passing a third argument to the `runBackgroundJob` function:

```php
runBackgroundJob(ExampleJob::class, 1, 60);
```

This example will delay the job for 60 seconds before it begins execution.

### Job Priority

You can configure the priority of the job. This can be useful if you have multiple jobs in the queue and want to control the order in which they are processed. You can set a priority by passing a fourth argument to the `runBackgroundJob` function:

```php
runBackgroundJob(ExampleJob::class, 1, 0, 10);
```

This will make the job a high priority, so it will be processed before other jobs in the queue.

## Example

Here’s a complete example using all of the configuration options:

```php
runBackgroundJob(ExampleJob::class, 3, 120, 10);
```

This example will:

Retry the job 3 times if it fails.
Delay the job by 120 seconds before it starts.
Process the job with high priority (priority 10).

## Testing and Logs

You can test the jobs by running the following commands:

```php
php job_runner.php --class="App\Jobs\ExampleJob" --method="process" --params='["Hello, Background Job!", 123]' --priority=1
php job_runner.php --class="App\Jobs\ExampleJob" --method="process" --params='["Hello, Background Job!", 123]' --priority=0
```

These commands will dispatch jobs with parameters and log the results.

## Dashboard

Register in the application once login you will see the `php Jobs ` navbar.
Click the link to view a page showing how a test job is running and whether it failed. This page helps you understand the process flow of background jobs in the system.

Planned Improvements:

User-Side Job Execution: In the future, we aim to enable users to execute background jobs directly from their side of the application.

## Conclusion

The `runBackgroundJob` function is a powerful and flexible tool for managing background tasks within your application. It provides a simple way to offload tasks without relying on Laravel’s queue system, offering full control over retries, delays, and priorities. By implementing background jobs in this manner, you can ensure that your application remains responsive and efficient, even as it handles long-running tasks.
