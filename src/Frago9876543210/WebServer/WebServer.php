<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use Exception;
use pocketmine\Thread;
use raklib\utils\InternetAddress;

class WebServer extends Thread
{
    /** @var resource $socket */
    protected $socket;
    /** @var callable $handler */
    protected $handler;
    /** @var InternetAddress $bindAddress */
    protected $bindAddress;
    /** @var bool $isRunning */
    protected $isRunning = true;

    /**
     * WebServer constructor.
     * @param InternetAddress $bindAddress
     * @param callable $handler
     * @throws Exception
     */
    public function __construct(InternetAddress $bindAddress, callable $handler)
    {
        $this->bindAddress = $bindAddress;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (@!socket_bind($this->socket, $bindAddress->getIp(), $bindAddress->getPort())) {
            throw new Exception("Failed to bind to $bindAddress");
        }
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_listen($this->socket);
        $this->handler = $handler;
    }

    public function run(): void
    {
        $this->registerClassLoader();

        while ($this->isRunning) {
            if (is_resource(($client = socket_accept($this->socket)))) {
                $connection = new Connection($client);
                call_user_func($this->handler, $connection, $connection->read());
            }
        }
    }

    /**
     * @return InternetAddress
     */
    public function getBindAddress(): InternetAddress
    {
        return $this->bindAddress;
    }

    /**
     * Disables socket processing
     */
    public function shutdown(): void
    {
        $this->isRunning = false;
    }
}