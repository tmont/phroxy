# Phroxy
[![Build Status](https://travis-ci.org/tmont/phroxy.png)](https://travis-ci.org/tmont/phroxy)

__Phroxy__ is a proxy generator. It's probably useful. You can use it to create
a mock object, or to create a proxy and intercept methods on that proxy.

## Installation
User composer:

```json
{
  "require": {
    "tmont/phroxy": "1.1.x"
  }
}
```

## Usage
I don't feel like writing documentation, so take a look at the
[unit tests](./tests/ProxyTest.php). There are examples of basic
proxying as well as advanced method interception.

The basic gist is:

```php
use Tmont\Phroxy\Interceptor;
use Tmont\Phroxy\InterceptorCache;
use Tmont\Phroxy\InterceptionContext;
use ReflectionClass;

class ReturnBeforeCallInterceptor implements Interceptor {
	public function onBeforeMethodCall(InterceptionContext $context) {
		$context->setReturnValue('oh hai!');
	}


	public function onAfterMethodCall(InterceptionContext $context) {}
}

class MyClass {
	public function hello() {
		return 'hello';
	}
}

$interceptor = new ReturnBeforeCallInterceptor();
InterceptorCache::registerInterceptor($interceptor, function($x) { return true; });

$proxy = $this->builder->build(new ReflectionClass('MyClass'));
$proxy->hello(); // "oh hai!"
```

## Development
```bash
git clone git@github.com:tmont/phroxy.git
cd phroxy
composer install
vendor/bin/phpunit
```