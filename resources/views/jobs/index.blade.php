<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Job Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <h2 class="text-lg font-semibold">Active Jobs</h2>
                    <div class="mb-4">
                        <form method="POST" action="{{ route('jobs.test') }}">
                            @csrf
                            <button type="submit" class="text-white hover:bg-blue-700 py-2 px-4 rounded" 
                            style="color: rgb(225 15 15);">
                                Dispatch Test Job
                            </button>
                        </form>
                    </div>
                    <table class="table-auto w-full border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2">ID</th>
                                <th class="border border-gray-300 px-4 py-2">Payload</th>
                                <th class="border border-gray-300 px-4 py-2">Attempts</th>
                                <th class="border border-gray-300 px-4 py-2">Created At</th>
                                <th class="border border-gray-300 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jobs as $job)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2">{{ $job->id }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        {{ json_decode($job->payload)->displayName ?? json_decode($job->payload)->data->commandName ?? 'N/A' }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $job->attempts }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $job->created_at }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <form method="POST" action="{{ route('jobs.cancel', $job->id) }}">
                                            @csrf
                                            <button class="text-red-500 hover:text-red-700" type="submit">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <h2 class="text-lg font-semibold mt-8">Failed Jobs</h2>
                    <table class="table-auto w-full border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2">ID</th>
                                <th class="border border-gray-300 px-4 py-2">Exception</th>
                                <th class="border border-gray-300 px-4 py-2">QUEUE</th>
                                <th class="border border-gray-300 px-4 py-2">Failed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($failedJobs as $failedJob)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2">{{ $failedJob->id }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                    <div class="whitespace-pre-wrap font-mono text-sm text-red-600">
                                        @php
                                            $exception = $failedJob->exception;
                                            // Extract the main error message
                                            $mainError = strstr($exception, 'Stack trace:', true) ?: $exception;
                                            // Get the stack trace
                                            $stackTrace = strstr($exception, 'Stack trace:');
                                        @endphp
                                        <div class="font-bold mb-2">{{ $mainError }}</div>
                                    </div>
                                </td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $failedJob->queue }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $failedJob->failed_at }}</td>
                                </tr>
                            @endforeach
                            @if($failedJobs->isEmpty())
                                <tr>
                                    <td colspan="3" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                                        No failed jobs found
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
