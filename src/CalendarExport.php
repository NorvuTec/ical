<?php

namespace Norvutec\ical;

use Norvutec\ical\Format\Formatter;
use Norvutec\ical\Model\Calendar;
use Norvutec\ical\Stream\CalendarStream;

class CalendarExport {

    /**
     * Calendars to be exported
     * @var array<Calendar>
     */
    private array $calendars = [];

    private CalendarStream $stream;
    private Formatter $formatter;

    private string $dateTimeFormat = "local";

    public function __construct(?CalendarStream $stream = null, ?Formatter $formatter = null) {
        $this->stream = $stream ?: new CalendarStream();
        $this->formatter = $formatter ?: new Formatter();
    }

    public function getStream(): string {

    }

}