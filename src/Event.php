<?php

namespace Bitty\EventManager;

use Bitty\EventManager\EventInterface;

class Event implements EventInterface
{
    /**
     * @var string
     */
    private $name = null;

    /**
     * @var string|object|null
     */
    private $target = null;

    /**
     * @var mixed[]
     */
    private $params = null;

    /**
     * @var bool
     */
    private $isPropagationStopped = false;

    /**
     * @param string $name
     * @param string|object|null $target
     * @param mixed[] $params
     */
    public function __construct(string $name, $target = null, array $params = [])
    {
        $this->setName($name);
        $this->setTarget($target);
        $this->setParams($params);
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): void
    {
        if (!preg_match("/^[A-Za-z0-9_\.]+$/", $name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Event name "%s" is invalid. Only alpha-numeric characters, '
                    .'underscores, and periods allowed.',
                    $name
                )
            );
        }

        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritDoc}
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * {@inheritDoc}
     */
    public function getParam(string $name)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stopPropagation(bool $flag): void
    {
        $this->isPropagationStopped = $flag;
    }

    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
