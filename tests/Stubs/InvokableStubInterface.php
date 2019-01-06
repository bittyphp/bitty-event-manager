<?php

namespace Bitty\Tests\EventManager\Stubs;

interface InvokableStubInterface
{
    /**
     * Mock invokable.
     *
     * @return callable|string
     */
    public function __invoke();
}
