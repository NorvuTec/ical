<?php

namespace Norvutec\ical\Tests;

use Norvutec\ical\CalendarUtil;
use Norvutec\ical\Stream\CalendarStream;
use PHPUnit\Framework\TestCase;

final class ExampleImportTest extends TestCase {

    public function testFromSchulferienOrg(): void {
        $this->assertFileExists(__DIR__ ."/files/ferien_niedersachsen_2025.ics");
        $file = file_get_contents(__DIR__ ."/files/ferien_niedersachsen_2025.ics");
        $calendar = CalendarUtil::import(new CalendarStream($file));
        $this->assertCount(1, $calendar);
        $this->assertCount(9, $calendar[0]->getEvents());
    }
}