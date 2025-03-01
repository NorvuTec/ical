<?php

namespace Norvutec\ical\Exception;

class IndexOutOfBoundsException extends IcalException {

    public function __construct(int $requestedIndex, int $maxIndex) {
        parent::__construct("Requested index $requestedIndex is out of bounds. Max index is $maxIndex");
    }

}