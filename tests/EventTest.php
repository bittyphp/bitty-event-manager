<?php

namespace Bitty\Tests\EventManager;

use Bitty\EventManager\Event;
use Bitty\EventManager\EventInterface;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @var Event
     */
    protected $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Event(uniqid());
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(EventInterface::class, $this->fixture);
    }

    public function testGetName(): void
    {
        $name = uniqid('AB_cd.1234');

        $this->fixture->setName($name);

        $actual = $this->fixture->getName();

        self::assertEquals($name, $actual);
    }

    public function testSetNameThrowsException(): void
    {
        $name = uniqid('name').'?';

        $message = 'Event name "'.$name.'" is invalid. Only alpha-numeric '
            .'characters, underscores, and periods allowed';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->fixture->setName($name);
    }

    public function testGetTarget(): void
    {
        $target = uniqid('target');

        $this->fixture->setTarget($target);

        $actual = $this->fixture->getTarget();

        self::assertEquals($target, $actual);
    }

    public function testGetParams(): void
    {
        $params = [uniqid(), uniqid()];

        $this->fixture->setParams($params);

        $actual = $this->fixture->getParams();

        self::assertEquals($params, $actual);
    }

    public function testGetParam(): void
    {
        $name   = uniqid('name');
        $value  = uniqid('value');
        $params = [uniqid() => uniqid(), $name => $value, uniqid() => uniqid()];

        $this->fixture->setParams($params);

        $actual = $this->fixture->getParam($name);

        self::assertEquals($value, $actual);
    }

    public function testGetParamNotSet(): void
    {
        $actual = $this->fixture->getParam(uniqid());

        self::assertNull($actual);
    }

    public function testPropagationStopped(): void
    {
        $this->fixture->stopPropagation(true);

        $actual = $this->fixture->isPropagationStopped();

        self::assertTrue($actual);
    }

    public function testPropagationNotStopped(): void
    {
        $this->fixture->stopPropagation(false);

        $actual = $this->fixture->isPropagationStopped();

        self::assertFalse($actual);
    }
}
