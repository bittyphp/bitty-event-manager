<?php

namespace Bitty\Tests\EventManager;

use Bitty\EventManager\Event;
use Bitty\EventManager\EventInterface;
use Bitty\EventManager\EventManager;
use Bitty\EventManager\EventManagerInterface;
use Bitty\Tests\EventManager\Stubs\InvokableStubInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventManagerTest extends TestCase
{
    /**
     * @var EventManager
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new EventManager();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(EventManagerInterface::class, $this->fixture);
    }

    public function testAttachReturnsTrue(): void
    {
        $actual = $this->fixture->attach(uniqid(), $this->createCallback());

        self::assertTrue($actual);
    }

    public function testCorrectCallbacksTriggered(): void
    {
        $nameA     = uniqid('name');
        $nameB     = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();
        $callbackC = $this->createCallback();
        $callbackD = $this->createCallback();

        $this->fixture->attach($nameA, $callbackA);
        $this->fixture->attach($nameB, $callbackB);
        $this->fixture->attach($nameB, $callbackC);
        $this->fixture->attach($nameA, $callbackD);

        $callbackB->expects(self::never())->method('__invoke');
        $callbackC->expects(self::never())->method('__invoke');

        $callbackA->expects(self::once())
            ->method('__invoke')
            ->with(self::isInstanceOf(EventInterface::class));

        $callbackD->expects(self::once())
            ->method('__invoke')
            ->with(self::isInstanceOf(EventInterface::class));

        $this->fixture->trigger($nameA);
    }

    public function testMultipleTriggers(): void
    {
        $name     = uniqid('name');
        $callback = $this->createCallback();

        $this->fixture->attach($name, $callback);

        $callback->expects(self::exactly(2))
            ->method('__invoke')
            ->with(self::isInstanceOf(EventInterface::class));

        $this->fixture->trigger($name);
        $this->fixture->trigger($name);
    }

    public function testCallbacksTriggeredInCorrectOrder(): void
    {
        $name      = uniqid('name');
        $callbackA = $this->createCallback('A');
        $callbackB = $this->createCallback('B');
        $callbackC = $this->createCallback('C');
        $callbackD = $this->createCallback('D');
        $callbackE = $this->createCallback('E');
        $callbackF = $this->createCallback('F');

        $this->fixture->attach($name, $callbackA, 1);
        $this->fixture->attach($name, $callbackB, 10);
        $this->fixture->attach($name, $callbackC, -10);
        $this->fixture->attach($name, $callbackD, 0);
        $this->fixture->attach($name, $callbackE);
        $this->fixture->attach($name, $callbackF, 0);

        $event = new Event($name);
        $this->fixture->trigger($event);

        self::assertEquals(['B', 'A', 'D', 'E', 'F', 'C'], $event->getParams());
    }

    public function testDetachCallback(): void
    {
        $name      = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();
        $callbackC = $this->createCallback();

        $this->fixture->attach($name, $callbackA);
        $this->fixture->attach($name, $callbackB);
        $this->fixture->attach($name, $callbackB);
        $this->fixture->attach($name, $callbackC);
        $this->fixture->detach($name, $callbackB);

        $callbackB->expects(self::never())->method('__invoke');

        $callbackA->expects(self::once())
            ->method('__invoke')
            ->with(self::isInstanceOf(EventInterface::class));

        $callbackC->expects(self::once())
            ->method('__invoke')
            ->with(self::isInstanceOf(EventInterface::class));

        $this->fixture->trigger($name);
    }

    public function testDetachSuccess(): void
    {
        $name     = uniqid('name');
        $callback = $this->createCallback();

        $this->fixture->attach($name, $callback);

        $actual = $this->fixture->detach($name, $callback);

        self::assertTrue($actual);
    }

    public function testDetachNonExistentEvent(): void
    {
        $actual = $this->fixture->detach(uniqid(), $this->createCallback());

        self::assertFalse($actual);
    }

    public function testDetachNonExistentCallback(): void
    {
        $name      = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();

        $this->fixture->attach($name, $callbackA);

        $actual = $this->fixture->detach($name, $callbackB);

        self::assertFalse($actual);
    }

    public function testClearListeners(): void
    {
        $nameA     = uniqid('name');
        $nameB     = uniqid('name');
        $callbackA = $this->createCallback();
        $callbackB = $this->createCallback();
        $callbackC = $this->createCallback();

        $this->fixture->attach($nameA, $callbackA);
        $this->fixture->attach($nameB, $callbackB);
        $this->fixture->attach($nameA, $callbackC);

        $this->fixture->clearListeners($nameA);

        $callbackA->expects(self::never())->method('__invoke');
        $callbackC->expects(self::never())->method('__invoke');

        $this->fixture->trigger($nameA);

        $callbackB->expects(self::once())
            ->method('__invoke')
            ->with(self::isInstanceOf(EventInterface::class));

        $this->fixture->trigger($nameB);
    }

    public function testClearNonExistentListeners(): void
    {
        try {
            $this->fixture->clearListeners(uniqid());
        } catch (\Throwable $e) {
            self::fail($e->getMessage());
        }

        self::assertTrue(true);
    }

    public function testTriggerNonExistentEvent(): void
    {
        $actual = $this->fixture->trigger(uniqid());

        self::assertFalse($actual);
    }

    public function testTriggerReturnsCallbackResponse(): void
    {
        $name      = uniqid('name');
        $response  = uniqid('response');
        $callbackA = $this->createMock(InvokableStubInterface::class);
        $callbackA->method('__invoke')->willReturn(uniqid());
        $callbackB = $this->createMock(InvokableStubInterface::class);
        $callbackB->method('__invoke')->willReturn($response);

        $this->fixture->attach($name, $callbackA, 1);
        $this->fixture->attach($name, $callbackB);

        $actual = $this->fixture->trigger($name);

        self::assertEquals($response, $actual);
    }

    public function testEventPropagationStopped(): void
    {
        $name      = uniqid('name');
        $callbackA = $this->createMock(InvokableStubInterface::class);
        $callbackA->method('__invoke')->willReturnCallback(
            function (EventInterface $event) {
                $event->stopPropagation(true);
            }
        );
        $callbackB = $this->createCallback();

        $this->fixture->attach($name, $callbackA, 1);
        $this->fixture->attach($name, $callbackB);

        $callbackA->expects(self::once())->method('__invoke');
        $callbackB->expects(self::never())->method('__invoke');

        $this->fixture->trigger($name);
    }

    /**
     * Creates a callback.
     *
     * @param string|null $name
     *
     * @return InvokableStubInterface|MockObject
     */
    private function createCallback(?string $name = null): InvokableStubInterface
    {
        $callback = $this->createMock(InvokableStubInterface::class);
        $callback->method('__invoke')->willReturnCallback(
            function (EventInterface $event) use ($name) {
                $params   = $event->getParams();
                $params[] = $name;
                $event->setParams($params);

                return function () {
                };
            }
        );

        return $callback;
    }
}
