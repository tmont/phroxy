<?php

	namespace Phroxy\Tests;
	
	use ReflectionClass, Exception, stdClass, ReflectionMethod;
	use Phroxy\ProxyBuilder;
	use Phroxy\InterceptorCache;
	use Phroxy\Interceptor;
	use Phroxy\InterceptionContext;

	class ProxyTest extends \PHPUnit_Framework_TestCase {
		
		private $builder;
		
		public function setup() {
			$this->builder = new ProxyBuilder();
			InterceptorCache::reset();
			ClassToBeProxied::reset();
		}
		
		public function tearDown() {
			InterceptorCache::reset();
			$this->builder = null;
		}
		
		public function testFinalMethodsAreNotIntercepted() {
			$interceptor1 = $this->getMock('Phroxy\Interceptor');
			$interceptor1->expects($this->never())->method('onBeforeMethodCall');
			$interceptor1->expects($this->never())->method('onAfterMethodCall');
			
			InterceptorCache::registerInterceptor($interceptor1, function($x) { return true; });
			
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			self::assertType('Phroxy\Tests\ClassToBeProxied', $proxy);
			
			$proxy->foo();
			self::assertEquals(1, ClassToBeProxied::$fooCalled);
		}
		
		public function testInterceptorBreaksOnBeforeMethodCall() {
			$interceptor1 = $this->getMock('Phroxy\Interceptor');
			$interceptor1->expects($this->once())->method('onBeforeMethodCall');
			$interceptor1->expects($this->never())->method('onAfterMethodCall');
			
			$interceptor2 = new FakeInterceptor();
			
			InterceptorCache::registerInterceptor($interceptor1, function($x) { return true; });
			InterceptorCache::registerInterceptor($interceptor2, function($x) { return true; });
			
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			$proxy->bar();
		}
		
		public function testSettingExceptionWithoutPreventingNextStillCallsParent() {
			InterceptorCache::registerInterceptor(new ExceptionInterceptor(), function($x) { return true; });
			
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			
			try {
				$proxy->bar();
				self::fail('Exception should have been thrown');
			} catch (Exception $e) {
				self::assertEquals('oh hai!', $e->getMessage());
			}
			
			self::assertEquals(1, ClassToBeProxied::$barCalled);
		}
		
		public function testSettingExceptionAndtPreventingNextDoesNotCallParent() {
			InterceptorCache::registerInterceptor(new ExceptionAndPreventNextInterceptor(), function($x) { return true; });
			
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			
			try {
				$proxy->bar();
				self::fail('Exception should have been thrown');
			} catch (Exception $e) {
				self::assertEquals('oh hai!', $e->getMessage());
			}
			
			self::assertEquals(0, ClassToBeProxied::$barCalled);
		}
		
		public function testInterceptorFilter() {
			$interceptor1 = $this->getMock('Phroxy\Interceptor');
			$interceptor1->expects($this->once())->method('onBeforeMethodCall');
			$interceptor1->expects($this->once())->method('onAfterMethodCall');

			$interceptor2 = $this->getMock('Phroxy\Interceptor');
			$interceptor2->expects($this->never())->method('onBeforeMethodCall');
			$interceptor2->expects($this->never())->method('onAfterMethodCall');

			InterceptorCache::registerInterceptor($interceptor1, function(ReflectionMethod $x) { return $x->getName() === 'bar'; });
			InterceptorCache::registerInterceptor($interceptor2, function(ReflectionMethod $x) { return $x->getName() === 'baz'; });

			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			$proxy->bar();
		}
		
		public function testAfterCallReturnValueOverridesDefaultReturnValue() {
			$interceptor = new ReturnAfterCallInterceptor();
			InterceptorCache::registerInterceptor($interceptor, function($x) { return true; });
			
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			self::assertEquals('oh hai!', $proxy->bar());
		}
		
		public function testBeforeCallReturnValueDoesNotOverrideDefaultReturnValue() {
			$interceptor = new ReturnBeforeCallInterceptor();
			InterceptorCache::registerInterceptor($interceptor, function($x) { return true; });
			
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassToBeProxied'));
			self::assertEquals('bar', $proxy->bar());
		}
		
		public function testFinalClassesCannotBeProxied() {
			$this->setExpectedException('Phroxy\ProxyException');
			$this->builder->build(new ReflectionClass('Phroxy\Tests\Unproxyable1'));
		}
		
		public function testAbstractClassesCannotBeProxied() {
			$this->setExpectedException('Phroxy\ProxyException');
			$this->builder->build(new ReflectionClass('Phroxy\Tests\Unproxyable2'));
		}
		
		public function testInterfacesCannotBeProxied() {
			$this->setExpectedException('Phroxy\ProxyException');
			$this->builder->build(new ReflectionClass('Phroxy\Tests\Unproxyable3'));
		}
		
		public function testUninstantiableClassesCannotBeProxied() {
			$this->setExpectedException('Phroxy\ProxyException');
			$this->builder->build(new ReflectionClass('Phroxy\Tests\Unproxyable4'));
		}
		
		public function testCreateProxyWithArgs() {
			$proxy = $this->builder->build(new ReflectionClass('Phroxy\Tests\ClassWithArgs'), array('foo'));
			self::assertType('Phroxy\Tests\ClassWithArgs', $proxy);
			self::assertEquals('foo', $proxy->foo);
		}
		
	}
	
	final class Unproxyable1 {}
	abstract class Unproxyable2 {}
	interface Unproxyable3 {}
	class Unproxyable4 { private function __construct() {} }
	
	class ClassWithArgs {
		public $foo;
		public function __construct($foo) {
			$this->foo = $foo;
		}
	}
	
	class ClassToBeProxied {
		
		public static $barCalled = 0;
		public static $fooCalled = 0;
		public static $bazCalled = 0;
		
		public final function foo() {
			self::$fooCalled++;
		}
		
		public function bar() {
			self::$barCalled++;
			return 'bar';
		}
		
		public function &baz(&$arg, array $arg2, stdClass $arg3 = null, $arg4 = 7) {
			self::$bazCalled++;
			return $arg2;
		}
		
		public static function reset() {
			self::$barCalled = 0;
			self::$fooCalled = 0;
			self::$bazCalled = 0;
		}
		
	}
	
	class ReturnBeforeCallInterceptor implements Interceptor {
		public function onBeforeMethodCall(InterceptionContext $context) {
			$context->setReturnValue('oh hai!');
		}
		
		
		public function onAfterMethodCall(InterceptionContext $context) {
		}
	}
	class ReturnAfterCallInterceptor implements Interceptor {
		public function onBeforeMethodCall(InterceptionContext $context) {
			
		}
		
		public function onAfterMethodCall(InterceptionContext $context) {
			$context->setReturnValue('oh hai!');
		}
	}
	
	class FakeInterceptor implements Interceptor {
		public function onBeforeMethodCall(InterceptionContext $context) {
			$context->callNext(false);
			$context->setReturnValue('oh hai!');
		}
		
		
		public function onAfterMethodCall(InterceptionContext $context) {}
	}
	
	class ExceptionInterceptor implements Interceptor {
		public function onBeforeMethodCall(InterceptionContext $context) {
			$context->setException(new Exception('oh hai!'));
		}
		
		
		public function onAfterMethodCall(InterceptionContext $context) {}
	}
	
	class ExceptionAndPreventNextInterceptor implements Interceptor {
		public function onBeforeMethodCall(InterceptionContext $context) {
			$context->setException(new Exception('oh hai!'));
			$context->callNext(false);
		}
		
		
		public function onAfterMethodCall(InterceptionContext $context) {}
	}
	
?>