# Logger Client
[![GitHub license](https://img.shields.io/github/license/flash-global/logger-client.svg)](https://github.com/flash-global/logger-client)![continuousphp](https://img.shields.io/continuousphp/git-hub/flash-global/logger-client.svg)[![GitHub issues](https://img.shields.io/github/issues/flash-global/logger-client.svg)](https://github.com/flash-global/logger-client/issues)

## Installation

Just add the following requirement to your `composer.json` file:

```
    "fei/logger-client": "^1.2.0"
```

## Configuration

The logger client needs some options to work properly. The available options that can be passed to the `__construct()` or `setOptions()` methods are :


| Option           | Description                                                                | Type   | Possible Values                                | Default                 |
|------------------|----------------------------------------------------------------------------|--------|------------------------------------------------|-------------------------|
| OPTION_BASEURL   | This is the server to which send the requests.                             | string | Any URL, including protocol but excluding path | --                      |
| OPTION_FILTER    | Minimum notification level required for notifications to be actually sent. | int    | Any Notification::LVL_* constant               | Notification::LVL_ERROR |
| OPTION_BACKTRACE | Should backtrace be added to notifications before they are sent.           | bool   | true / false                                   | true                    |
| OPTION_LOGFILE   | File path and name where the Logger will store its own exceptions.         | string | Any writeable file  path                       | /tmp/logger.log         |
| OPTION_HEADER_AUTHORIZATION    | Api Key for authentification                                               | string | Any string value                               | ''                      |

Notes:
*Logger is an alias of Fei\Service\Logger\Client\Logger*
*Notification is an alias of Fei\Service\Logger\Entity\Notification*

## Usage

### Initialization

A Logger client should always be initialized by a dependency injection component, since it requires at least one dependency, which is the transport. Moreover, the BASEURL parameter should also depends on environment.

```php
// sample configuration for production environment
$logger = new Logger(array(
                            Logger::OPTION_BASEURL  => 'http://logger.flash-global.net',
                            Logger::OPTION_FILTER   => Notification::LVL_DEBUG,
                          )
                    );
// inject transport classes
$logger->setTransport(new BasicTransport());

// optionnal asynchronous transport, that will be automatically used to push notifications
//
// NOTE this transport requires a beanstalk queue able to listen to its requests
$pheanstalk = new Pheanstalk('localhost');
$asyncTransport = new BeanstalkProxyTransport;
$asyncTransport->setPheanstalk($pheanstalk);
$logger->setAsyncTransport($asyncTransport);
```


### Pushing a simple notification

Once you have set up the Logger, you can start pushing notifications by calling the `notify()` method on the Logger:

```php

$logger = $container->get('logger');

$logger->notify('Notification message'); // default level is Notification::LVL_INFO
$logger->notify('Debug message', array('level' => Notification::LVL_DEBUG));
```

While its possible to pass more than just the level using the second (array) parameter, it is recommended not to do so. If you want to pass more informations, like a context, please take a look at the following section.

### Pushing a Notification instance

The more reliable way to push a notification is to instantiate it by yourself, and then send it through `notify()`, that will also accept Notification instances:

```php

$logger = $container->get('logger');

$notification = new Notification(array('message' => 'Notification message'));
$notification
        ->setLevel(Notification::LVL_WARNING)
        ->setContext(array('key' => 'value')
        ;
        
$logger->notify($notification);
```

### PSR-3 Adapter

PSR-3 describe an interface for logging purpose to ensure interoperability between systems.

For this end we provide the adapter `Fei\Service\Logger\Client\Psr\PsrLoggerAdapter`.

```php
<?php

use Fei\Service\Logger\Client\Logger;
use Fei\Service\Logger\Client\Psr\PsrLoggerAdapter;

$logger = new Logger();

$psr = new PsrLoggerAdapter($logger);

$psr->error('This is a error message');
```

It's always possible to set category, namespace and other notification properties with log context: 

```php
<?php

use Fei\Service\Logger\Client\Logger;
use Fei\Service\Logger\Client\Psr\PsrLoggerAdapter;
use Fei\Service\Logger\Entity\Notification;

$logger = new Logger();

$psr = new PsrLoggerAdapter($logger);

$psr->error(
    'This is a error message',
    [
        'namespace' => '/my/app',
        'category' => Notification::TRACKING,
        'key1' => 'value1',
        'key2' => 'value2'
    ]
);
```

Manageable Notification properties are `flag`, `namespace`, `user`, `server`, `command`, `origin`, `category` and `env`.
Another key of context will be set in Notification context.
