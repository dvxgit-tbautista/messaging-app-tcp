<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use React\EventLoop\Factory as EventLoopFactory;

class SocketServer extends Command
{
    protected $signature = 'socket:server';
    protected $description = 'Start a simple socket server';

    public function handle()
    {
        require base_path('vendor/autoload.php');

        $loop = EventLoopFactory::create();

        $server = new Server('127.0.0.1:8080', $loop);

        $server->on('connection', function (ConnectionInterface $connection) use ($loop) {
            $this->info("New connection from {$connection->getRemoteAddress()}");

            $connection->on('data', function ($data) use ($connection) {
                $this->info("Received: {$data}");
                $filePath = public_path('server.txt');
                file_put_contents($filePath, $data);
            });

            $connection->on('close', function () use ($connection) {
                $this->info("Connection closed: {$connection->getRemoteAddress()}");
            });

            // Send a message to the client every 2 seconds
            $loop->addPeriodicTimer(1, function () use ($connection) {
                $filePath = public_path('server.txt');
                $message = file_get_contents($filePath);
                if (!empty($message)) {
                    $this->info("Sending: {$message}");
                    // Clear the contents of the 'server.txt' file
                    file_put_contents($filePath, '');
                    $connection->write($message);
                }
            });
        });

        $this->info("Server listening on 127.0.0.1:8080");
        $loop->run();
    }
}
