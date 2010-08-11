<?php
	
	/**
	 * Interceptor
	 *
	 * @package   Phroxy
	 * @version   1.0
	 * @copyright (c) 2010 Tommy Montgomery
	 */

	namespace Phroxy;

	/**
	 * Interface for method interceptors on proxied types
	 *
	 * These objects can be registered on a container so that when a
	 * type is resolved and proxied, whenever a method on that proxy is
	 * invoked, each interceptor associated with that method will be
	 * invoked before and after the method invocation.
	 *
	 * @package Phroxy
	 */
	interface Interceptor {
	
		/**
		 * Called before the method is invoked
		 *
		 * @param InterceptionContext $context
		 */
		function onBeforeMethodCall(InterceptionContext $context);
		
		/**
		 * Called after the method is invoked
		 *
		 * @param InterceptionContext $context
		 */
		function onAfterMethodCall(InterceptionContext $context);
	}
	
?>