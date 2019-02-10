<?php

namespace Bitty\EventManager;

use Bitty\EventManager\Event;
use Bitty\EventManager\EventInterface;
use Bitty\EventManager\EventManagerInterface;

class EventManager implements EventManagerInterface
{
    /**
     * @var array[]
     */
    private $callbacks = [];

    /**
     * @var string[]
     */
    private $sorted = [];

    /**
     * {@inheritDoc}
     */
    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        $this->callbacks[$event][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        $this->sorted[$event] = '';

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function detach(string $event, callable $callback): bool
    {
        if (!isset($this->callbacks[$event])) {
            return false;
        }

        $indexes = [];
        foreach ($this->callbacks[$event] as $index => $data) {
            if ($callback !== $data['callback']) {
                continue;
            }

            $indexes[] = $index;
        }

        foreach ($indexes as $index) {
            unset($this->callbacks[$event][$index]);
        }

        return !empty($indexes);
    }

    /**
     * {@inheritDoc}
     */
    public function clearListeners(string $event): void
    {
        if (!isset($this->callbacks[$event])) {
            return;
        }

        unset($this->callbacks[$event]);
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($event, $target = null, array $params = [])
    {
        if ($event instanceof EventInterface) {
            $name = $event->getName();
        } else {
            $name  = $event;
            $event = new Event($name, $target, $params);
        }

        if (!isset($this->callbacks[$name])) {
            return false;
        }

        $this->sortCallbacks($name);

        $response = null;
        foreach ($this->callbacks[$name] as $data) {
            $response = $data['callback']($event, $response);

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $response;
    }

    /**
     * Sorts event callbacks, if not already sorted.
     *
     * @param string $event
     */
    private function sortCallbacks(string $event): void
    {
        if (!empty($this->sorted[$event])) {
            return;
        }

        usort($this->callbacks[$event], function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        $this->sorted[$event] = $event;
    }
}
