<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use pocketmine\plugin\PluginBase;
use raklib\utils\InternetAddress;

class Main extends PluginBase{
	public function onEnable() : void{
		try{
			$server = new WebServer(new InternetAddress("0.0.0.0", 8080, 4),
				function(Connection $connection, string $buffer){
					echo $buffer . PHP_EOL;
					$connection->send("text/html", "<h1>PHP WebServer</h1>");
					$connection->close();
				}
			);
			$server->start();
		}catch(\Exception $e){
			$this->getLogger()->critical($e->getMessage());
		}finally{
			if(isset($server)){
				$this->getLogger()->notice("The WebServer was successfully started on " . $server->getBindAddress()->toString());
			}
		}
	}
}