<?php

namespace Norvutec\ical\Stream;

use Norvutec\ical\Exception\IndexOutOfBoundsException;
use Norvutec\ical\Model\Calendar;

/**
 * Reader for a {@link CalendarStream} that reads the stream line by line
 * Is used for importing a calendar file by {@link Calendar}
 */
class CalendarStreamReader {

    private CalendarStream $stream;
    private array $lines = [];
    private int $currentLine = 0;

    public function __construct(CalendarStream $stream) {
        $this->stream = $stream;
        $this->importLines();
    }

    /**
     * Imports the lines from the current {@link CalendarStream}
     * @return void
     */
    private function importLines(): void {
        $fileContent = substr($this->stream->getStream(), 0, strlen("BEGIN:VCALENDAR")+20);
        if (preg_match("/\r\n/", $fileContent)) {
            $this->lines = explode(CalendarStream::CRLF, $this->stream->getStream());
        } elseif (preg_match("/\r/", $fileContent)) {
            $this->lines = explode("\\r", $this->stream->getStream());
        } elseif (preg_match("/\n/", $fileContent)) {
            $this->lines = explode("\\n", $this->stream->getStream());
        } else {
            // cant find any line endings to detect - using default
            $this->lines = explode(CalendarStream::CRLF, $this->stream->getStream());
        }
    }

    /**
     * Resets the current line position to the beginning
     * @return void
     */
    public function seek(): void {
        $this->currentLine = 0;
    }

    /**
     * Moves the current line position back by one
     * @return CalendarStreamReader
     */
    public function back(): self {
        $this->currentLine--;
        return $this;
    }

    /**
     * Checks if there is a next line to read
     * @return bool
     */
    public function hasNext(): bool {
        return $this->currentLine < count($this->lines);
    }

    /**
     * Reads the next line from the stream
     * @return string The next line
     * @throws IndexOutOfBoundsException If there is no next line
     */
    public function readLine(): string {
        if(!$this->hasNext()) {
            throw new IndexOutOfBoundsException(($this->currentLine+1), count($this->lines));
        }
        $nextLine = $this->lines[$this->currentLine];
        $this->currentLine++;
        if($this->hasNext()) {
            $addNextLine = $this->lines[$this->currentLine];
            while (str_starts_with($addNextLine, ' ')) {
                $nextLine .= substr($addNextLine, 1);
                $this->currentLine++;
                $addNextLine = $this->lines[$this->currentLine];
            }
        }
        return $nextLine;
    }

}