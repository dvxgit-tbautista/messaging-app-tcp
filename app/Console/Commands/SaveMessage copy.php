<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SaveMessage extends Command
{
    protected $signature = 'message:save';
    protected $description = 'Save messages to server.txt file';

    public function handle()
    {
        $this->info("Enter your message (Press Enter on an empty line to save and exit):");

        $message = '';
        while (true) {
            $input = $this->readTerminalInput();
            echo "Server: " . $input . "\n";
            $filePath = public_path('server.txt');
            file_put_contents($filePath, $input);

            if (empty($input)) {
                // Save the message to 'client.txt'
                $filePath = public_path('server.txt');
                file_put_contents($filePath, $input);
                echo $input . '\n';

                $this->info("Message saved to 'server.txt'. Exiting...");
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
