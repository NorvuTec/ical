<?php

namespace Norvutec\ical\Exception;

class EmptyStreamException extends IcalException {

    public function __construct() {
        parent::__construct("The stream is empty");
    }

}