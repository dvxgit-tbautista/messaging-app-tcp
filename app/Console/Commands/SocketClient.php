<?php

namespace App\Console\Commands;

use App\Models\Messages;
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

        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector->connect('tcp://127.0.0.1:8080')->then(function (\React\Socket\ConnectionInterface $connection) use ($loop) {
            $this->info("Connected to the server");

            // Fetch server messages from the database and send them to the server
            $fetchMessages = function () use ($connection) {
                $serverMessages = Messages::where('processed', 0)->get();

                foreach ($serverMessages as $serverMessage) {
                    $this->info("Sending: {$serverMessage->messages}");
                    $connection->write($serverMessage->messages);

                    // Mark the message as processed in the database
                    $serverMessage->update(['processed' => 1]);
                }
            };

            // Fetch messages initially and then set up a periodic timer
            $fetchMessages();
            $loop->addPeriodicTimer(1, $fetchMessages);
        });

        $loop->run();
    }
}
