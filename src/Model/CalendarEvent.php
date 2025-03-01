<?php

namespace Norvutec\ical\Model;

use Norvutec\ical\Constants;
use Norvutec\ical\Exception\IcalException;
use Norvutec\ical\Exception\UnexpectedCalendarDataException;
use Norvutec\ical\Stream\CalendarStream;
use Norvutec\ical\Stream\CalendarStreamReader;

class CalendarEvent {

    private string $uid;
    private \DateTime $dtStamp;
    private \DateTime $dtStart;
    private ?\DateTime $dtEnd = null;

    /**
     * Writes the event to the stream
     * @param CalendarStream $stream The stream to write to
     * @return void
     */
    public function write(CalendarStream $stream): void {
        $stream->addItem("BEGIN:VEVENT")
            ->addItem("UID:" . $this->uid)
            ->addItem("DTSTAMP:" . $this->dtStamp->format(Constants::DT_FORMAT));
        if($this->dtStart->format('His') === "000000") {
            $stream->addItem("DTSTART:VALUE=DATE:" . $this->dtStart->format(Constants::D_FORMAT));
        }else{
            $stream->addItem("DTSTART:" . $this->dtStart->format(Constants::DT_FORMAT));
        }
        if($this->dtEnd !== null) {
            if($this->dtEnd->format('His') === "000000") {
                $stream->addItem("DTEND:VALUE=DATE:" . $this->dtEnd->format(Constants::D_FORMAT));
            }else{
                $stream->addItem("DTEND:" . $this->dtEnd->format(Constants::DT_FORMAT));
            }
        }
        //https://www.rfc-editor.org/rfc/rfc5545.html#section-3.6.1

        $stream->addItem("END:VEVENT");
    }

    /**
     * Reads the event from the stream
     * @param CalendarStreamReader $reader The reader to read from
     * @throws IcalException
     */
    public function read(CalendarStreamReader $reader): self {
        if(!$reader->hasNext()) return $this;
        $firstLine = $reader->readLine();
        if($firstLine !== "BEGIN:VEVENT") {
            throw new UnexpectedCalendarDataException("BEGIN:VEVENT", $firstLine);
        }
        while($reader->hasNext()) {
            $line = $reader->readLine();
            if($line === "END:VEVENT") {
                return $this;
            }
            if(str_starts_with($line, "UID:")) {
                $this->uid = substr($line, 4);
                continue;
            }
            if(str_starts_with($line, "DTSTAMP:")) {
                $this->dtStamp = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 8));
                continue;
            }
            if(str_starts_with($line, "DTSTART:")) {
                if(str_contains($line, "VALUE=DATE")) {
                    $this->dtStart = \DateTime::createFromFormat(Constants::D_FORMAT, substr($line, 17));
                }else{
                    $this->dtStart = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 8));
                }
                continue;
            }
            if(str_starts_with($line, "DTEND:")) {
                if(str_contains($line, "VALUE=DATE")) {
                    $this->dtEnd = \DateTime::createFromFormat(Constants::D_FORMAT, substr($line, 15));
                }else{
                    $this->dtEnd = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 6));
                }
                continue;
            }
        }
        return $this;
    }

}