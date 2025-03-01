<?php

namespace Norvutec\ical\Exception;

class UnexpectedCalendarDataException extends IcalException {

    public function __construct(string $expected, string $actual) {
        parent::__construct("Unexpected calendar data. Expected $expected, got $actual");

    }

}