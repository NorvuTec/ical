<?php

namespace Norvutec\ical\Model;

/**
 * Classification of an calendar element
 */
enum CalendarEventClassification: string {

    case PUBLIC = 'PUBLIC';
    case PRIVATE = 'PRIVATE';
    case CONFIDENTIAL = 'CONFIDENTIAL';
    case IANA_TOKEN = "iana-token";
    case X_NAME = "x-name";

}