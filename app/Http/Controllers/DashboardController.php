<?php

namespace App\Http\Controllers;

use App\Jobs\TestJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $jobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->get();

        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->get();

        return view('jobs.index', compact('jobs', 'failedJobs'));
    }

    public function dispatchTest(Request $request)
    {
        $param1 = 'Test from dashboard';    
        $param2 = 123;            
        $retries = 3;             
        $executionDelay = 5;       
        $priority = 1;        
    
        // Dispatch the job with the given parameters and apply the delay
        TestJob::dispatch($param1, $param2, $retries, $executionDelay, $priority)
            ->delay(now()->addSeconds($executionDelay));
        
        return redirect()->route('jobs.index')
            ->with('success', 'Test job dispatched successfully');
    }

    public function cancel($id)
    {
        $job = DB::table('jobs')->where('id', $id)->first();

        if ($job && !$job->reserved_at) {

            DB::table('jobs')->where('id', $id)->delete();

            return redirect()->route('jobs.index')
                ->with('success', 'Job cancelled successfully.');
        }

        return redirect()->route('jobs.index')
            ->with('error', 'Unable to cancel the job. It might be processing already.');
    }
}
