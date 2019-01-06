<?php

namespace Bitty\Tests\EventManager\Stubs;

interface InvokableStubInterface
{
    /**
     * Mock invokable.
     */
    public function __invoke(): callable;
}
