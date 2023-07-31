<?php

namespace App\Console\Commands;

use App\Models\Messages;
use Illuminate\Console\Command;

class ReceiveMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receive:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tcpHost = '192.168.244.1';
        $tcpPort = '1234';

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, $tcpHost, $tcpPort);

        $connection = true;
        while ($connection) {
            // Read user input from the command line
            $message = readline("Enter your message: ");

            // Send the message to the server
            socket_write($socket, $message, strlen($message));

            $data = socket_read($socket, 1024, PHP_BINARY_READ);

            $data = Messages::create([
                'messages' => $data
            ]);

            if ($data->messages === false || empty($data->messages) || $data->messages == '') {
                // Error or connection closed
                $connection = false;
                break;
            }
            // Process the received data
            // echo "Received: " . $data . PHP_EOL;
            echo "Received: " . $data->messages . PHP_EOL;
        }
        socket_close($socket);
    }
}
