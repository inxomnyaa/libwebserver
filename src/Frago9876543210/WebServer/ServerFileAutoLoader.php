<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use BaseClassLoader;
use ClassLoader;

class ServerFileAutoLoader extends BaseClassLoader
{
    public function __construct(ClassLoader $parent = null)
    {
        parent::__construct($parent);
    }
}