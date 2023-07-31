<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessServerMessage;
use App\Models\Messages; // Add this line to include the Messages model

class SaveClientMessage extends Command
{
    protected $signature = 'message:save-client';
    protected $description = 'Save client messages to the queue';

    public function handle()
    {
        $this->info("Enter your message (Press Enter on an empty line to save and exit):");

        $message = '';
        while (true) {
            $input = $this->readTerminalInput();
            echo "Client: " . $input . "\n";

            // Save the message to the database before enqueuing for processing
            $query = Messages::create(['messages' => $input, 'processed' => 0]);
            // dd($query);
            // Enqueue the message to be processed by the socket server
            ProcessServerMessage::dispatch($message);
            $this->info("Message added to the queue for processing.");

            // Append the input to the message variable
            $message .= $input . PHP_EOL;
        }
    }

    private function readTerminalInput()
    {
        $stdin = fopen('php://stdin', 'r');
        $input = trim(fgets($stdin));
        fclose($stdin);
        return $input;
    }
}
