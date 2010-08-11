<?php

	/**
	 * InvalidConstructorException
	 *
	 * @package   Phroxy
	 * @version   1.0
	 * @copyright (c) 2010 Tommy Montgomery
	 */

	namespace Phroxy;

	use Exception;
	
	/**
	 * Exception that is raised when trying to register or resolve types
	 * that have an invalid constructor
	 *
	 * @package Phroxy
	 */
	class InvalidConstructorException extends Exception {}

?>
