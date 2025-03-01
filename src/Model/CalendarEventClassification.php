<?php

namespace Norvutec\ical\Model;

enum CalendarEventClassification: string {

    case PUBLIC = 'PUBLIC';
    case PRIVATE = 'PRIVATE';
    case CONFIDENTIAL = 'CONFIDENTIAL';
    case IANA_TOKEN = "iana-token";
    case X_NAME = "x-name";

}