<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

/**
 * Class WSRequest
 * @package Frago9876543210\WebServer
 * @link https://github.com/ClanCatsStation/PHPWebserver/blob/master/src/Request.php
 */
class WSRequest implements StatusCodes
{
	/** @var string The raw request data */
	protected $rawData;
	/** @var string The request method */
	protected $method;
	/** @var string The requested uri */
	protected $uri;
	/** @var string The requested version */
	protected $version;
	/** @var array The request params */
	protected $parameters = [];
	/** @var array The request headers */
	protected $headers = [];

	/**
	 * Create new request instance using the socket read data
	 * @param string $rawData
	 * @return self
	 * @throws SocketException
	 */
	public static function constructFromRawData(string $rawData): self
	{
		if ($rawData === '') throw new SocketException('No more bytes left or connection closed');
		$lines = explode("\n", $rawData);
		// method, uri, version
		$requestInfo = explode(' ', array_shift($lines));
		$method = $requestInfo[0] ?? null;
		$uri = $requestInfo[1] ?? '/';
		$version = $requestInfo[2] ?? null;
		// headers
		$headers = [];
		foreach ($lines as $line) {
			// clean the line
			$line = trim($line);
			if (strpos($line, ': ') !== false) {
				[$key, $value] = explode(': ', $line);
				$headers[$key] = $value;
			}
		}
		// create new request object
		return new static($rawData, $method, $uri, $headers, $version);
	}

	/**
	 * WSRequest constructor.
	 * @param string $rawData
	 * @param null|string $method
	 * @param string $uri
	 * @param null|array $headers
	 * @param null|string $version
	 */
	public function __construct(string $rawData, ?string $method = 'GET', string $uri = '/', ?array $headers = [], ?string $version = 'HTTP/1.1')
	{
		$this->rawData = $rawData;
		if ($method === null) $method = 'GET';
		if ($headers === null) $headers = [];
		if ($version === null) $version = 'HTTP/1.1';
		$this->headers = $headers;
		$this->method = strtoupper($method);
		$this->version = strtoupper($version);
		$this->uri = str_replace('/', DIRECTORY_SEPARATOR, parse_url($uri)['path'] ?? '');
		if ($method === 'GET')
			parse_str(parse_url($uri)['query'] ?? '', $this->parameters);
		else if ($method === 'POST') {
			$stripos = stripos($rawData, PHP_EOL . PHP_EOL);
			if ($stripos === false) return;
			$data = substr($rawData, $stripos+2);
			if ($data === false) return;
			#foreach (explode(';', ($headers['Content-Type'].';')) as $type) {
			#	$type = strtolower(trim($type));//Todo support more types
			#	var_dump($type);
			if (($parsed = parse_ini_string($data)) !== false) {//TODO find something to replace parse_ini
				$this->parameters = $parsed;
				return;
			}
			#}
		}

	}

	/**
	 * Return the request method
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * Return the request uri
	 * @return string
	 */
	public function getUri(): string
	{
		return $this->uri;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Return a request header
	 * @param string $key
	 * @param null|mixed $default
	 * @return null|string
	 */
	public function getHeader(string $key, $default = null): ?string
	{
		return $this->headers[$key] ?? $default;
	}

	/**
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Return a request parameter
	 * @param string $key
	 * @param null|mixed $default
	 * @return null|string
	 */
	public function getParam(string $key, $default = null): ?string
	{
		return $this->parameters[$key] ?? $default;
	}

	/**
	 * Return the protocol version
	 * @return string
	 */
	public function getVersion(): ?string
	{
		return $this->version;
	}
}