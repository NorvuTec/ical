<?php

namespace Norvutec\ical\Stream;

use Norvutec\ical\Exception\IndexOutOfBoundsException;

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
        $this->lines = explode(CalendarStream::CRLF, $this->stream->getStream());
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
     * @return void
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
        return $nextLine;
    }

}