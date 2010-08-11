<?php

	/**
	 * Bootstrapper for Phroxy
	 *
	 * @package   Phroxy
	 * @version   1.0
	 * @copyright (c) 2010 Tommy Montgomery
	 */

	namespace Phroxy;
	
	spl_autoload_register(function($className) {
		$file = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
		if (is_file($file)) {
			require_once $file;
		}
	});

?>