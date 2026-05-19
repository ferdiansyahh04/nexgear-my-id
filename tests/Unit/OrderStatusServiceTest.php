<?php

namespace Tests\Unit;

use App\Libraries\OrderStatusService;
use CodeIgniter\Test\CIUnitTestCase;

class OrderStatusServiceTest extends CIUnitTestCase
{
    public function testValidForwardTransitions(): void
    {
        $this->assertTrue(OrderStatusService::canTransition('checked_out', 'paid'));
        $this->assertTrue(OrderStatusService::canTransition('paid', 'processing'));
        $this->assertTrue(OrderStatusService::canTransition('processing', 'shipped'));
        $this->assertTrue(OrderStatusService::canTransition('shipped', 'delivered'));
    }

    public function testCancellationOnlyFromEarlyStatuses(): void
    {
        $this->assertTrue(OrderStatusService::canTransition('checked_out', 'cancelled'));
        $this->assertTrue(OrderStatusService::canTransition('paid', 'cancelled'));
        $this->assertFalse(OrderStatusService::canTransition('shipped', 'cancelled'));
        $this->assertFalse(OrderStatusService::canTransition('delivered', 'cancelled'));
    }

    public function testCannotSkipStages(): void
    {
        $this->assertFalse(OrderStatusService::canTransition('checked_out', 'shipped'));
        $this->assertFalse(OrderStatusService::canTransition('paid', 'delivered'));
    }

    public function testTimelineMarksCurrentStageActive(): void
    {
        $timeline = OrderStatusService::timelineFor('paid');
        $byKey = [];
        foreach ($timeline as $s) $byKey[$s['key']] = $s['state'];

        $this->assertSame('reached', $byKey['checked_out']);
        $this->assertSame('active',  $byKey['paid']);
        $this->assertSame('pending', $byKey['shipped']);
    }

    public function testCancelledTimelineIsSingleStage(): void
    {
        $timeline = OrderStatusService::timelineFor('cancelled');
        $this->assertCount(1, $timeline);
        $this->assertSame('cancelled', $timeline[0]['state']);
    }
}
