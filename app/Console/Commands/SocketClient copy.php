<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\EventLoop\Factory;
use React\Socket\Connector;

class SocketClient extends Command
{
    protected $signature = 'socket:client';
    protected $description = 'Connect to the server using sockets';

    public function handle()
    {
        require base_path('vendor/autoload.php');

        // $message = file_get_contents('client.txt');

        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector->connect('tcp://127.0.0.1:8080')->then(function (\React\Socket\ConnectionInterface $connection) use ($loop) {
            $this->info("Connected to the server");

            $connection->on('data', function ($data) {
                $this->info("Received: {$data}");
            });

            $loop->addPeriodicTimer(1, function () use ($connection) {
                $filePath = public_path('client.txt');
                $message = file_get_contents($filePath);

                // Only send a message if it is not empty
                if (!empty($message)) {
                    $this->info("Sending: {$message}");
                    $connection->write($message);

                    // No need to display the received message from the server here
                    // since it will be handled by the 'data' event callback above.

                    // Clear the contents of the text file after sending the message
                    file_put_contents($filePath, '');
                }
            });
        });

        $loop->run();
    }
}
