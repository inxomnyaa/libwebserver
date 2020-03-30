<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use InvalidArgumentException;
use raklib\utils\InternetAddress;

class WSConnection
{
    /** @var resource $socket */
    protected $socket;

    /**
     * WSConnection constructor.
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
        $read = @socket_read($this->socket, 1024);
        if ($read === false) {
            print socket_strerror(socket_last_error($this->socket));
            $this->close();
            return '';
        }
        return $read;
    }

    /**
     * @param string $data
     */
    public function write(string $data): void
    {
        @socket_write($this->socket, $data);
    }

    /**
     * Closes the connection
     */
    public function close(): void
    {
        @socket_close($this->socket);
    }

    /**
     * @param WSResponse $response
     */
    public function send(WSResponse $response): void
    {
        $this->write($response->getResponse());
    }

    /**
     * @return null|InternetAddress
     */
    public function getAddress(): ?InternetAddress
    {
        try {
            return @socket_getpeername($this->socket, $address, $port) ? new InternetAddress($address, $port, 4) : null;
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}