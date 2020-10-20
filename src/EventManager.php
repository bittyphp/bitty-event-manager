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
     * @var bool[]
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

        $detached = false;
        foreach ($this->callbacks[$event] as $index => $data) {
            if ($callback === $data['callback']) {
                unset($this->callbacks[$event][$index]);
                $detached = true;
            }
        }
        
        if (empty($this->callbacks[$event])) {
            $this->clearListeners($event);
        }

        return $detached;
    }

    /**
     * {@inheritDoc}
     */
    public function clearListeners(string $event): void
    {
        unset($this->callbacks[$event], $this->sorted[$event]);
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
        if (! $this->sorted[$name]) {
            // order by priority: from lowest to highest 
            array_multisort($this->callbacks[$name], array_column($this->callbacks[$name], 'priority'));
            // fix order: from highest to lowest
            $this->callbacks[$name] = array_reverse($this->callbacks[$name]);
            
            $this->sorted[$name] = true;
        }
    }
}
