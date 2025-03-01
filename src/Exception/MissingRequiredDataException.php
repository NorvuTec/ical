<?php

namespace Norvutec\ical\Exception;

class MissingRequiredDataException extends IcalException {

    public function __construct(string $data) {
        parent::__construct("Missing required data in field: $data");
    }

}