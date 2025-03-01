<?php

namespace Norvutec\ical\Tests;

use Norvutec\ical\CalendarUtil;
use Norvutec\ical\Exception\MissingRequiredDataException;
use Norvutec\ical\Model\Calendar;
use Norvutec\ical\Model\CalendarEvent;
use Norvutec\ical\Model\CalendarEventClassification;
use PHPUnit\Framework\TestCase;

final class CheckingExportAndImportTest extends TestCase {

    public function testExportCalendar() : void {
        $calendar = $this->getTestCalendar();

        $tStream = CalendarUtil::export($calendar);
        $this->assertNotNull($tStream);
    }

    public function testExportAndImportCalendar(): void {
        $calendar = $this->getTestCalendar();

        $tStream = CalendarUtil::export($calendar);
        $calendars = CalendarUtil::import($tStream);
        $this->assertCount(1, $calendars);
        $this->assertCount(count($calendar->getEvents()), $calendars[0]->getEvents());
    }

    public function testMissingRequiredFields(): void {
        $this->expectException(MissingRequiredDataException::class);
        $calendar = new Calendar();
        $event1 = new CalendarEvent();
        $event1->setUid("EVENT-1-TESTCASE");
        $calendar->addEvent($event1);
        CalendarUtil::export($calendar);
    }

    private function getTestCalendar(): Calendar {
        $calendar = new Calendar();

        $event1 = new CalendarEvent();
        $event1->setDtStamp(new \DateTime());
        $event1->setUid("EVENT-1-TESTCASE");
        $event1->setSummary("Test Event 1");
        $event1->setDescription("This is a test event 1");
        $event1->setDtStart(new \DateTime());
        $event1->getDtStart()->setTime(12, 0, 0);
        $event1->setDtEnd(new \DateTime());
        $event1->getDtEnd()->setTime(15, 0, 0);
        $event1->setClass(CalendarEventClassification::PRIVATE);
        $calendar->addEvent($event1);

        return $calendar;
    }

}