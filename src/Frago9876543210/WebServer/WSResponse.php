<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

/**
 * Class WSResponse
 * @package Frago9876543210\WebServer
 * @link https://github.com/ClanCatsStation/PHPWebserver/blob/master/src/Response.php
 */
class WSResponse implements StatusCodes
{

    /**
     * Returns a simple response based on a status code
     * @param int $status
     * @return self
     */
    public static function error(int $status): self
    {
        return new static("<h1>$status - " . self::CODES[$status] . '</h1>', $status);
    }

    /** @var string The response version */
    protected $version = 'HTTP/1.1';
    /** @var int The current response status */
    protected $status = 200;
    /** @var string The current response body */
    protected $body = '';
    /** @var array The current response headers */
    protected $headers = [];

    /**
     * Construct a new Response object
     * @param string $body
     * @param int $status
     * @param string $contentType
     */
    public function __construct(string $body, int $status = null, string $contentType = '')
    {
        if ($status !== null) {
            $this->status = $status;
        }
        $this->body = $body;
        // set initial headers
        $this->header('Date', gmdate('D, d M Y H:i:s T'));
        $this->header('Content-Type', $contentType);
        $this->header('Server', 'libwebserver');
        $this->header('Status', "$this->status " . self::CODES[$this->status]);
        $this->header('Status-Code', "$this->status " . self::CODES[$this->status]);
        $this->header('Status-Line', "$this->version $this->status " . self::CODES[$this->status]);
        $this->header('Cache-Control', 'max-age=0');
    }

    /**
     * Return the response body
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Add or overwrite an header parameter header
     * @param string $key
     * @param string $value
     * @return void
     */
    public function header($key, $value): void
    {
        $this->headers[ucfirst($key)] = $value;
    }

    /**
     * Build a header string based on the current object
     * @return string
     */
    public function buildHeaderString(): string
    {
        $lines = [];
        // response status
        $lines[] = "{$this->version} {$this->status} " . self::CODES[$this->status];
        // add the headers
        foreach ($this->headers as $key => $value) {
            $lines[] = "$key: $value";
        }
        return implode(" \r\n", $lines) . "\r\n\r\n";
    }

    /**
     * Create a string out of the response data
     * @return string
     */
    public function getResponse(): string
    {
        return $this->buildHeaderString() . $this->body();
    }

    /**
     * Create a string out of the response data
     * @return string
     */
    public function __toString()
    {
        return $this->buildHeaderString() . $this->body();
    }
}