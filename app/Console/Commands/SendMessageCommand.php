<?php

namespace App\Console\Commands;

use App\Models\Messages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMessageCommand extends Command
{
    protected $signature = 'send:message';
    protected $description = 'Send messages over TCP socket';
    // $tcpHost = '192.168.244.1';

    public function handle()
    {
        $tcpHost = '192.168.244.1';
        $tcpPort = '1234';

        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

            if ($socket === false) {
                throw new \Exception('Socket creation failed: ' . socket_strerror(socket_last_error()));
            } else {
                $this->info('Success');
            }

            if (!socket_connect($socket, $tcpHost, $tcpPort)) {
                throw new \Exception('Socket connection failed: ' . socket_strerror(socket_last_error($socket)));
            } else {
                $this->info('Connected');
            }

            while (true) {

                $messageData = "Greetings, I am your client, and it is expected that I remain active at all times without any disconnections.";

                $message = Messages::create([
                    'messages' => $messageData
                ]);

                sleep(3);
                $message->delete();

                socket_write($socket, $messageData, strlen($messageData));

                // Read the server's response
                $response = socket_read($socket, 2048); // Adjust the buffer size according to your requirements

                $this->info('Server response: ' . $response);

                // Here you can add more logic to handle the server's response if needed.

                // Sleep for a while before sending the next message
                sleep(3);
            }

            // socket_write($socket, $messageData, strlen($messageData));

            // socket_close($socket);

            // $this->info('Messages sent successfully!');
        } catch (\Exception $e) {
            Log::error('Error sending messages: ' . $e->getMessage());
            $this->error('An error occurred while sending messages.');
        }
    }
}
