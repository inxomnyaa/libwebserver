# libwebserver
Lightweight websocket based PHP web server

## Example
This is my current debugging code
```php
// Main.php
private function startFileServer():void
{
    $serverRoot = $this->getDataFolder() . "wwwroot";
    $ws = \Frago9876543210\WebServer\API::startWebServer($this, \Frago9876543210\WebServer\API::getPathHandler($serverRoot));
    $ws->getClassLoader()->getParent()->addPath(realpath($serverRoot),true);
    var_dump($ws->getClassLoader()->getParent()->loadClass("framework\Header"));
    var_dump($ws->getClassLoader()->getParent()->loadClass("website\account\API"));
    var_dump($ws->getClassLoader()->getParent()->findClass("framework\Header"));
    var_dump($ws->getClassLoader()->getParent()->findClass("website\account\API"));
    var_dump($ws->getClassLoader()->findClass("Frago9876543210\WebServer\WSConnection"));
    var_dump($ws->getClassLoader()->getParent());
}
```