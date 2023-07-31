<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use React\Socket\ConnectionInterface;
use Illuminate\Support\Facades\Log;

class ProcessServerMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Sending: {$this->message}");

        $loop = app(\React\EventLoop\LoopInterface::class);
        $connector = new \React\Socket\Connector($loop);

        // Connect to the socket server
        $connector->connect('tcp://127.0.0.1:8080')->then(function (ConnectionInterface $connection) {
            Log::info("Connected to the server");

            // Send the message to the socket server
            $connection->write($this->message);

            // Close the connection after sending the message
            $connection->end();
        });
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        // Log any errors or exceptions that occurred during job processing
        Log::error('Error processing server message: ' . $exception->getMessage());
    }
}
