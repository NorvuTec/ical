<?php

namespace Norvutec\ical;

use Norvutec\ical\Exception\EmptyStreamException;
use Norvutec\ical\Exception\IcalException;
use Norvutec\ical\Exception\UnexpectedCalendarDataException;
use Norvutec\ical\Model\Calendar;
use Norvutec\ical\Stream\CalendarStream;
use Norvutec\ical\Stream\CalendarStreamReader;

/**
 * Utility class for handling calendars import and export
 */
class CalendarUtil {

    /**
     * Exports the given calendars to a stream
     * @param Calendar ...$calendars The calendars to export
     * @return CalendarStream The stream containing the calendars
     */
    public static function export(Calendar... $calendars): CalendarStream {
        $stream = new CalendarStream();
        foreach($calendars as $calendar) {
            $calendar->write($stream);
        }
        return $stream;
    }

    /**
     * Imports the calendars from the given stream
     * @param CalendarStream $stream The stream to import from
     * @return array<Calendar> The imported calendars
     * @throws IcalException
     */
    public static function import(CalendarStream $stream): array {
        $reader = new CalendarStreamReader($stream);
        if(!$reader->hasNext()) {
            throw new EmptyStreamException();
        }
        $calendars = [];
        while($reader->hasNext()) {
            $tLine = $reader->readLine();
            if(str_starts_with($tLine, "BEGIN:VCALENDAR")) {
                $reader->back();
                $calendar = new Calendar();
                $calendar->read($reader);
                $calendars[] = $calendar;
            }else{
                throw new UnexpectedCalendarDataException("BEGIN:VCALENDAR", $tLine);
            }
        }
        return $calendars;
    }

}