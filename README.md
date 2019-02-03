# Bitty Event Manager

[![Build Status](https://travis-ci.org/bittyphp/event-manager.svg?branch=master)](https://travis-ci.org/bittyphp/event-manager)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/097658a564cb4402a406961041e29b4e)](https://www.codacy.com/app/bittyphp/event-manager)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Mutation Score](https://badge.stryker-mutator.io/github.com/bittyphp/event-manager/master)](https://infection.github.io)
[![Total Downloads](https://poser.pugx.org/bittyphp/event-manager/downloads)](https://packagist.org/packages/bittyphp/event-manager)
[![License](https://poser.pugx.org/bittyphp/event-manager/license)](https://packagist.org/packages/bittyphp/event-manager)

Bitty comes with an Event Manager that follows the proposed  [PSR-14](https://github.com/php-fig/fig-standards/blob/master/proposed/event-manager.md) standard. The event manager can be used to attach listeners to certain events or actions that happen. When the system triggers those events, all the listeners for that event will automatically be called.

This can be used for any number of things, some of which might include: triggering an alert on multiple failed logins, clearing a cache when the information has been updated, or maybe send an email when the status of something changes. There's unlimited possibilities for what you can do!

## Installation

It's best to install using [Composer](https://getcomposer.org/).

```sh
$ composer require bittyphp/event-manager
```

## Creating an Event

An event can contain three distinct pieces of information.

1. **Name** - The name of the event (required). Can only contain alphanumeric characters (A-Z, a-z, 0-9), underscores, and periods.
2. **Target** - Object, string, or null (optional). This is essentially the context for which the event was triggered. For example, if the event was "product.update" then this might be the product object that was updated.
3. **Parameters** - Array of additional parameters (optional). Any additional data related to the event.

Bitty only comes with one built-in event object. If you want to build your own event objects, they must implement the `Bitty\EventManager\EventInterface`.

```php
<?php

use Bitty\EventManager\Event;

// The long way
$event = new Event();
$event->setName('some.name');
$event->setTarget($someTarget);
$event->setParams(['param 1', 'param 2']);

// The short way
$event = new Event('some.name', $someTarget, ['param 1', 'param 2']);
```

## Attaching a Listener

A listener can be any callable or any invokable object. The listener will receive two parameters: 1) an instance of `Bitty\EventManager\EventInterface` and 2) the return value of any previous event or null if there was no previous event.

A listener may return a value, but is not required to. When the event is triggered, the return value of the last listener will be returned to whatever triggered the event. A simple use case of this would be returning `true` or `false` to determine if the listener succeeded in doing what it was supposed to.

### Basic Usage

```php
<?php

use Bitty\EventManager\EventInterface;
use Bitty\EventManager\EventManager;

$eventManager = new EventManager();

$eventManager->attach('some.event', function (EventInterface $event, $previous = null) {
    // Do stuff
});

// Or omit the parameters if you don't need them
$eventManager->attach('some.event', function () {
    // Do stuff
});
```

### Advanced Usage

If setting multiple listeners for an event, you can also set a priority level to determine which one should be triggered first. The higher the priority number, the more likely it will be called before other listeners. The default priority is zero.

```php
<?php

use Bitty\EventManager\EventManager;

$eventManager = new EventManager();

$eventManager->attach('some.event', function () {
    // This will trigger SECOND
});

$eventManager->attach('some.event', function () {
    // This will trigger LAST
}, -10);

$eventManager->attach('some.event', function () {
    // This will trigger FIRST
}, 10);
```

## Detaching a Listener

You can remove a specific listener for any event if you still have a reference to it.

```php
<?php

use Bitty\EventManager\EventManager;

$eventManager = new EventManager();

$callable = function () {
    // Do stuff
};

$eventManager->attach('some.event', $callable);

// Things happen...

$eventManager->detach('some.event', $callable);
```

## Clearing all Listeners

You can clear all of the listeners for any event at any time.

```php
<?php

use Bitty\EventManager\EventManager;

$eventManager = new EventManager();

$eventManager->clearListeners('some.event');
```

## Triggering Listeners

You can trigger the listeners tied to an event one of two ways: 1) passing in the data for the event and letting the event manager build the event for you, or 2) building the event yourself and then sending it to the event manager.

### Basic Usage

```php
<?php

use Bitty\EventManager\Event;
use Bitty\EventManager\EventManager;

$eventManager = new EventManager();

// Make the event manager build and then trigger the event
$eventManager->trigger('some.event', $someTarget, ['param 1', 'param 2']);

// Or build the event and have it triggered
$event = new Event('some.event', $someTarget, ['param 1', 'param 2']);
$eventManager->trigger($event);
```

## Event Propagation

If, for whatever reason, you need an event to stop propagating through the system, you can easily stop it at any time using the `stopPropagation()` method.

```php
<?php

use Bitty\EventManager\EventInterface;
use Bitty\EventManager\EventManager;

$eventManager = new EventManager();

$eventManager->attach('some.event', function (EventInterface $event) {
    // Do stuff

    $event->stopPropagation(true);
});

$eventManager->trigger('some.event');
```
