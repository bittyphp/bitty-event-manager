<?php

namespace Bitty\EventManager;

/**
 * Representation of an event
 *
 * NOTE: This is a placeholder until PSR-14 is approved.
 */
interface EventInterface
{
    /**
     * Set the event name
     *
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * Get event name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the event target
     *
     * @param string|object|null $target
     */
    public function setTarget($target): void;

    /**
     * Get target/context from which event was triggered
     *
     * @return string|object|null
     */
    public function getTarget();

    /**
     * Set event parameters
     *
     * @param array $params
     */
    public function setParams(array $params): void;

    /**
     * Get parameters passed to the event
     *
     * @return array
     */
    public function getParams(): array;

    /**
     * Get a single parameter by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam(string $name);

    /**
     * Indicate whether or not to stop propagating this event
     *
     * @param bool $flag
     */
    public function stopPropagation(bool $flag): void;

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;
}
