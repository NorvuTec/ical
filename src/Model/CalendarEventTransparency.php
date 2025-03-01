<?php

namespace Norvutec\ical\Model;

enum CalendarEventTransparency: string {
    case OPAQUE = 'OPAQUE';
    case TRANSPARENT = 'TRANSPARENT';
}