<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use Closure;
use ErrorException;
use Exception;
use pocketmine\plugin\Plugin;
use poggit\virion\devirion\DEVirion;
use raklib\utils\InternetAddress;
use Throwable;
use XenialDan\TestPlugin\CustomClassLoader;

class API
{
	public static function startWebServer(Plugin $plugin, Closure $handler, int $port = 8080): ?WebServer
	{
		try {
			$server = new WebServer(new InternetAddress('0.0.0.0', $port, 4), $handler);

			if (($dv = $plugin->getServer()->getPluginManager()->getPlugin('DEVirion')) !== null) {
				/** @var DEVirion $dv */
				$dv->getVirionClassLoader();
			}

			$cl = new CustomClassLoader();
			$server->setClassLoader($cl);

			$server->start(PTHREADS_INHERIT_NONE);

			$plugin->getLogger()->notice('The WebServer was successfully started on ' . $server->getBindAddress()->toString());
			return $server;
		} catch (Exception $e) {
			$plugin->getLogger()->critical($e->getMessage());
		}
		return null;
	}

	/**
	 * Use this if you want to send a basic default page
	 * You can use file_get_contents() to read and display a file.
	 * If you create your own handler, you could also access request
	 * data and modify the response data accordingly.
	 * @param string $content
	 * @return Closure
	 */
	public static function getTextResponseHandler(string $content = '<h1>PHP WebServer</h1>'): Closure
	{
		return static function (WSConnection $connection, WSRequest $request) use ($content): void {
			$connection->send(new WSResponse($content));
			$connection->close();
		};
	}

	/**
	 * Use this when you want a fully responsible webserver which can automatically
	 * access subfolders & files, display images and execute .php websites
	 * @param string $serverRoot
	 * @return Closure
	 */
	public static function getPathHandler(string $serverRoot): Closure
	{
		$serverRoot = realpath('/' . $serverRoot);
		return static function (WSConnection $connection, WSRequest $request) use ($serverRoot): void {
			//set root path
			set_include_path($serverRoot);
			$rtrim = rtrim(str_replace("\0", '', $serverRoot . $request->getUri()));
			$requestedPath = realpath($rtrim);
			if ($requestedPath === false) {
				$response = WSResponse::error(404);
				$connection->send($response);
				$connection->close();
				return;
			}

			set_error_handler(static function ($errno, $errstr, $errfile, $errline, $errcontext) {
				// error was suppressed with the @-operator
				if (0 === error_reporting()) {
					return false;
				}

				throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
			});

			try {
				chdir(dirname($requestedPath));
				if (is_dir($requestedPath)) {
					//is dir, search for index files
					$fileList = glob($requestedPath . DIRECTORY_SEPARATOR . 'index.{php,html,htm}', GLOB_MARK | GLOB_BRACE | GLOB_NOSORT);
				} else {
					//is file, search for file
					$fileList = glob($requestedPath, GLOB_MARK | GLOB_NOSORT);
				}
			} catch (ErrorException $e) {
				\GlobalLogger::get()->logException($e);
			}

			restore_error_handler();

			if (empty($fileList)) {
				$response = WSResponse::error(404);
			} else {
				$file = array_shift($fileList);
				//$fullPath = realpath(dirname($requestedPath) . DIRECTORY_SEPARATOR . $file);
				if (!is_file($file)) {
					$response = WSResponse::error(403);
				} else {
					try {
						ob_start(); // begin collecting output
						/** @noinspection PhpIncludeInspection */
						@include $file;
						$getContents = ob_get_clean();
					} catch (Throwable $e) {
						print $e->getMessage();
						print $e->getTraceAsString();
						$getContents = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
						#$connection->close();
						#return;
					}
					$response = new WSResponse($getContents);//TODO detect mime type
				}
			}
			$connection->send($response);
			$connection->close();
		};
	}
}