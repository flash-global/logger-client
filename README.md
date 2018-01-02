# Service Logger - Client

[![GitHub release](https://img.shields.io/github/release/flash-global/logger-client.svg?style=for-the-badge)](README.md) 


## Table of contents
- [Purpose](#purpose)
- [Requirements](#requirements)
    - [Runtime](#runtime)
- [Step by step installation](#step-by-step-installation)
    - [Initialization](#initialization)
    - [Settings](#settings)
    - [Known issues](#known-issues)
- [Link to documentation](#link-to-documentation)
    - [Examples](#examples)
- [Contribution](#contribution)

## Purpose
This client permit to use the `Logger Api`. Thanks to it, you could request the API to :
* Fetch data
* Create data

easily

## Requirements 

### Runtime
- PHP 5.5

## Step by step Installation
> for all purposes (development, contribution and production)

### Initialization
- Add the following requirement to your `composer.json` file:
```"fei/logger-client": "^1.2.0"```
- Run Composer depedencies installation
```composer install```

### Settings

Don't forget to set the right `baseUrl` in files located in examples.

```php
<?php 
// sample configuration for production environment
$logger = new Logger(
    [
        Logger::OPTION_BASEURL  => 'http://logger.flash-global.net',
        Logger::OPTION_FILTER   => Notification::LVL_DEBUG,
    ]
);
// inject transport classes
$logger->setTransport(new BasicTransport());
```

### Known issues
No known issue at this time.

## Link to documentation 

### Examples
You can test this client easily thanks to the folder [example](example)

Here, an example on how to use example : `php /my/logger-client/example/search.php` 

#### Pushing a simple notification

Once you have set up the Logger, you can start pushing notifications by calling the `notify()` method on the Logger:

```php

$logger = $container->get('logger');

$logger->notify('Notification message'); // default level is Notification::LVL_INFO
$logger->notify('Debug message', array('level' => Notification::LVL_DEBUG));
```

While its possible to pass more than just the level using the second (array) parameter, it is recommended not to do so. If you want to pass more informations, like a context, please take a look at the following section.

#### Pushing a Notification instance

The more reliable way to push a notification is to instantiate it by yourself, and then send it through `notify()`, that will also accept Logger instances:

```php

$logger = $container->get('logger');

$notification = new Notification(array('message' => 'Notification message'));
$notification
        ->setLevel(Notification::LVL_WARNING)
        ->setContext(array('key' => 'value')
        ;
        
$logger->notify($notification);
```

## Contribution
As FEI Service, designed and made by OpCoding. The contribution workflow will involve both technical teams. Feel free to contribute, to improve features and apply patches, but keep in mind to carefully deal with pull request. Merging must be the product of complete discussions between Flash and OpCoding teams :) 