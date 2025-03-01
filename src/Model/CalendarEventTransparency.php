<?php

namespace Norvutec\ical\Model;

/**
 * Transparency of an calendar element if its visible or transparent
 */
enum CalendarEventTransparency: string {
    case OPAQUE = 'OPAQUE';
    case TRANSPARENT = 'TRANSPARENT';
}