<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use Exception;
use pocketmine\plugin\Plugin;
use raklib\utils\InternetAddress;

class API
{
    public static function startWebServer(Plugin $plugin, callable $handler, int $port = 8080): ?WebServer
    {
        try {
            $server = new WebServer(new InternetAddress("0.0.0.0", $port, 4), $handler);
            $server->start();
        } catch (Exception $e) {
            $plugin->getLogger()->critical($e->getMessage());
        } finally {
            if (isset($server)) {
                $plugin->getLogger()->notice("The WebServer was successfully started on " . $server->getBindAddress()->toString());
                return $server;
            }
        }
        return null;
    }

    /**
     * Use this if you want to send a basic default page
     * You can use file_get_contents() to read and display a file.
     * If you create your own handler, you could also access request
     * data and modify the response data accordingly.
     * @param string $content
     * @return callable
     */
    public static function getTextResponseHandler(string $content = "<h1>PHP WebServer</h1>"): callable
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
     * @return callable
     */
    public static function getPathHandler(string $serverRoot): callable
    {
        return static function (WSConnection $connection, WSRequest $request) use ($serverRoot): void {
            $requestedFile = $request->getUri();
            $fullPath = realpath($serverRoot . $requestedFile);
            if ($fullPath === false || !is_file($fullPath))
                $fullPath = realpath($serverRoot . $requestedFile . DIRECTORY_SEPARATOR . "index.php");
            if ($fullPath === false || !is_file($fullPath))
                $fullPath = realpath($serverRoot . $requestedFile . DIRECTORY_SEPARATOR . "index.html");
            if ($fullPath === false) {
                $response = WSResponse::error(404);
            } else {
                if (!is_file($fullPath)) {
                    $response = WSResponse::error(403);
                } else {
                    ob_start(); // begin collecting output
                    include $fullPath;
                    $getContents = ob_get_clean();
                    @ob_end_clean();
                    $response = new WSResponse($getContents);//TODO detect mime type
                }
            }
            $connection->send($response);
            $connection->close();
        };
    }
}