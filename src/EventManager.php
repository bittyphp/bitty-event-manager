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
    protected $callbacks = [];

    /**
     * @var bool[]
     */
    protected $sorted = [];

    /**
     * {@inheritDoc}
     */
    public function attach(string $event, callable $callback, int $priority = 0): bool
    {
        $this->callbacks[$event][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        $this->sorted[$event] = false;

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

        $found = -1;
        foreach ($this->callbacks[$event] as $index => $data) {
            if ($callback === $data['callback']) {
                $found = $index;

                continue;
            }
        }

        if ($found > -1) {
            unset($this->callbacks[$event][$found]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function clearListeners(string $event): void
    {
        if (isset($this->callbacks[$event])) {
            unset($this->callbacks[$event]);
        }
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
    protected function sortCallbacks(string $event): void
    {
        if (!empty($this->sorted[$event])) {
            return;
        }

        usort($this->callbacks[$event], function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return 0;
            }

            return $a['priority'] < $b['priority'] ? 1 : -1;
        });

        $this->sorted[$event] = true;
    }
}
