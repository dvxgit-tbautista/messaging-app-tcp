<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\Messages;
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

            // Fetch messages from the database and send them to the client
            $fetchMessages = function () use ($connection) {
                $clientMessages = Messages::where('processed', 0)->get();

                foreach ($clientMessages as $clientMessage) {
                    $this->info("Sending: {$clientMessage->content}");
                    $connection->write($clientMessage->content);

                    // Mark the message as processed in the database
                    $clientMessage->update(['processed' => 1]);
                }
            };

            // Fetch messages initially and then set up a periodic timer
            $fetchMessages();
            $loop->addPeriodicTimer(1, $fetchMessages);

            $connection->on('data', function ($data) use ($connection) {
                $this->info("Received: {$data}");
                // You can process the received data from the client here if needed.
            });

            $connection->on('close', function () use ($connection) {
                $this->info("Connection closed: {$connection->getRemoteAddress()}");
            });
        });

        $this->info("Server listening on 127.0.0.1:8080");
        $loop->run();
    }
}
