<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SaveClientMessage extends Command
{
    protected $signature = 'message:save-client';
    protected $description = 'Save client messages to client.txt file';

    public function handle()
    {
        $this->info("Enter your message (Press Enter on an empty line to save and exit):");

        $message = '';
        while (true) {
            $input = $this->readTerminalInput();
            echo "Client: " . $input . "\n";
            $filePath = public_path('client.txt');
            file_put_contents($filePath, $input);

            if (empty($input)) {
                // Save the message to 'client.txt'
                $filePath = public_path('client.txt');
                file_put_contents($filePath, $input);
                // file_put_contents('client.txt', $input);
                echo $input . '\n';

                $this->info("Message saved to 'client.txt'. Exiting...");
                break;
            }

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
