<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use Exception;
use pocketmine\plugin\Plugin;
use raklib\utils\InternetAddress;

class API
{
    public function onEnable(): void
    {
        try {
            $server = new WebServer(new InternetAddress("0.0.0.0", 8080, 4),
                function (Connection $connection, string $buffer) {
                    echo $buffer . PHP_EOL;
                    $connection->send("text/html", "<h1>PHP WebServer</h1>");
                    $connection->close();
                }
            );
            $server->start();
        } catch (Exception $e) {
            $this->getLogger()->critical($e->getMessage());
        } finally {
            if (isset($server)) {
                $this->getLogger()->notice("The WebServer was successfully started on " . $server->getBindAddress()->toString());
            }
        }
    }

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
}