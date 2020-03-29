<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use InvalidArgumentException;
use raklib\utils\InternetAddress;

class Connection
{
    /** @var resource $socket */
    protected $socket;

    /**
     * Connection constructor.
     * @param resource $socket
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    /**
     * @return string
     */
    public function read(): string
    {
        return socket_read($this->socket, 1024);
    }

    /**
     * @param string $data
     */
    public function write(string $data): void
    {
        socket_write($this->socket, $data);
    }

    /**
     * Closes the connection
     */
    public function close(): void
    {
        socket_close($this->socket);
    }

    /**
     * @param string $type
     * @param string $data
     */
    public function send(string $type, string $data)
    {
        $this->write("HTTP/1.1 200 OK\r\nContent-Type: " . $type . "\r\n\r\n" . $data);
    }

    /**
     * @return null|InternetAddress
     */
    public function getAddress(): ?InternetAddress
    {
        try {
            return socket_getpeername($this->socket, $address, $port) ? new InternetAddress($address, $port, 4) : null;
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}