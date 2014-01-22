<?php

	/**
	 * ObjectBuilder
	 *
	 * @package   Phroxy
	 * @version   1.0
	 * @copyright (c) 2010 Tommy Montgomery
	 */

	namespace Tmont\Phroxy;
	
	use ReflectionClass;

	/**
	 * Builds objects. Duh.
	 *
	 * @package Phroxy
	 */
	interface ObjectBuilder {
	
		/**
		 * Dynamically creates an instance of the given class
		 *
		 * @param  ReflectionClass $class The class to create
		 * @param  array           $args  Constructor arguments
		 * @return object
		 */
		function build(ReflectionClass $class, array $args = array());
	}

?>