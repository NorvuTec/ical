<?php

namespace Norvutec\ical\Stream;

/**
 * A Stream that holds the content of the ical file as output or input
 */
class CalendarStream {

    /**
     * Maximum line length in bytes
     */
    const MAXIMUM_LINE_LENGTH = 70;
    const CRLF = "\r\n";

    /**
     * @var string The content stream of the ical calendar
     */
    private string $stream = "";

    public function __construct(?string $fileContent = null) {
        if($fileContent != null) {
            $this->stream = $fileContent;
        }
    }

    /**
     * Resets the ical stream
     * @return void
     */
    public function reset(): void {
        $this->stream = "";
    }

    /**
     * Returns the ical stream
     * @return string The content stream of the ical calendar
     */
    public function getStream(): string {
        return $this->stream;
    }

    /**
     * Adds an item/line to the calendar stream
     * Will split the line if required to fit the maximum line length
     * @param string $item The item to add
     * @return $this The current instance
     */
    public function addItem(string $item): self {
        // Replace newlines to literal \n
        $item = str_replace("\n", "\\n", str_replace("\r\n", "\n", $item));

        $length = strlen($item);

        $block = '';

        if ($length > 75) {
            $start = 0;

            while ($start < $length) {
                $cut = mb_strcut($item, $start, self::MAXIMUM_LINE_LENGTH, 'UTF-8');
                $block .= $cut;
                $start = $start + strlen($cut);

                //add space if not last line
                if ($start < $length) {
                    $block .= self::CRLF.' ';
                }
            }
        } else {
            $block = $item;
        }

        $this->stream .= $block.self::CRLF;

        return $this;
    }

    public function __toString(): string {
        return $this->getStream();
    }

}