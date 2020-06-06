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
            $plugin->getLogger()->notice("The WebServer was successfully started on " . $server->getBindAddress()->toString());
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
            //set root path
            set_include_path($serverRoot);
            $serverRoot = realpath($serverRoot);

            $requestedFile = '.' . $request->getUri();
            if (is_dir($serverRoot . $request->getUri())) {
                //is dir, search for index files
                $parentDir = $requestedFile;
                chdir($serverRoot . $parentDir);
                $fileList = glob("index.{php,html,htm}", GLOB_MARK | GLOB_BRACE | GLOB_NOSORT);
            } else {
                //is file, search for file
                $parentDir = dirname($requestedFile);
                chdir($serverRoot . $parentDir);
                $fileList = glob(basename($requestedFile), GLOB_MARK | GLOB_NOSORT);
            }

            if (empty($fileList)) {
                $fullPath = false;
            } else {
                $str = getcwd() . DIRECTORY_SEPARATOR . array_shift($fileList);
                $fullPath = realpath($str);
            }
            if ($fullPath === false) {
                $response = WSResponse::error(404);
            } else {
                if (!is_file($fullPath)) {
                    $response = WSResponse::error(403);
                } else {
                    try {
                        ob_start();
                        @include $fullPath;
                        $getContents = ob_get_clean();
                        ob_start(); // begin collecting output
                    } catch (\Throwable $e) {
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