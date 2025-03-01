<?php

namespace Norvutec\ical\Exception;

class InvalidArgumentException extends IcalException {

    public function __construct(string $message) {
        parent::__construct($message);
    }

}