<?php

declare(strict_types=1);

namespace Frago9876543210\WebServer;

use BaseClassLoader;
use poggit\virion\devirion\VirionClassLoader;

class CustomClassLoader extends VirionClassLoader
{

	public function __construct(BaseClassLoader $loader1,?VirionClassLoader $vl = null)
	{
		var_dump($this);
		parent::__construct();
		if($vl!== null){
			$closure = function () { return $this->mappedClasses; };
			$closure2 = function () { return $this->antigenMap; };
			$mapped = $closure->call($vl);
			$anti = $closure2->call($vl);
			$closure3 = function ($mapped) { foreach ($mapped as $k => $v) $this->mappedClasses[$k] = $v; };
			$closure4 = function ($anti) { foreach ($anti as $k => $v) $this->antigenMap[$k] = $v; };
			$closure3->call($this,$mapped);
			$closure4->call($this,$anti);
		}
		$lookup = $loader1->getAndRemoveLookupEntries();
		foreach ($lookup as $item) {
			$this->addPath($item);
		}
		var_dump($this);
	}

	public function findClass($name): ?string
	{
		$class = parent::findClass($name);
		if ($class !== null) return $class;
		return BaseClassLoader::findClass($name);
	}

	/**
	 * Called when there is a class to load
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function loadClass($name): ?bool
	{
		return BaseClassLoader::loadClass($name);
	}
}