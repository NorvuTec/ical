<?php

namespace Norvutec\ical\Model;

use DateTime;
use Norvutec\ical\Constants;
use Norvutec\ical\Exception\IcalException;
use Norvutec\ical\Exception\InvalidArgumentException;
use Norvutec\ical\Exception\MissingRequiredDataException;
use Norvutec\ical\Exception\UnexpectedCalendarDataException;
use Norvutec\ical\Stream\CalendarStream;
use Norvutec\ical\Stream\CalendarStreamReader;

/**
 * Represents a calendar event placed inside a {@link Calendar}
 */
class CalendarEvent {

    private string $uid;
    private \DateTime $dtStamp;
    private \DateTime $dtStart;
    private \DateTime $dtEnd;
    private ?CalendarEventClassification $class = null;
    private ?DateTime $created = null;
    private ?DateTime $lastModified = null;
    private ?string $summary = null;
    private ?string $description = null;
    private ?string $location = null;
    private ?CalendarEventGeoLocation $geoLocation = null;
    private ?string $organizer = null; // @TODO split into detailed options for organizer
    private ?int $priority = 0;
    private ?string $status = null;
    private ?CalendarEventTransparency $transparency = null;
    private ?string $url = null;
    //@TODO Recurrence & Sequence
    private ?string $comment = null;
    private ?string $contact = null; // @TODO split into detailed options for contact

    /**
     * @var array<string> lines of the import that are not known
     */
    private array $unknownImportLines = [];

    /**
     * Writes the event to the stream
     * @param CalendarStream $stream The stream to write to
     * @return void
     * @throws MissingRequiredDataException
     */
    public function write(CalendarStream $stream): void {
        if(!isset($this->uid)) {
            throw new MissingRequiredDataException("uid");
        }
        if(!isset($this->dtStamp)) {
            throw new MissingRequiredDataException("dtStamp");
        }
        if(!isset($this->dtStart)) {
            throw new MissingRequiredDataException("dtStart");
        }
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
        if($this->class) {
            $stream->addItem("CLASS:" . $this->class->value);
        }
        if($this->created) {
            $stream->addItem("CREATED:" . $this->created->format(Constants::DT_FORMAT));
        }
        if($this->lastModified) {
            $stream->addItem("LAST-MODIFIED:" . $this->lastModified->format(Constants::DT_FORMAT));
        }
        if($this->summary) {
            $stream->addItem("SUMMARY:" . $this->summary);
        }
        if($this->description) {
            $stream->addItem("DESCRIPTION:" . $this->description);
        }
        if($this->location) {
            $stream->addItem("LOCATION:" . $this->location);
        }
        if($this->geoLocation) {
            $stream->addItem("GEO:" . $this->geoLocation->getLatitude() . ";" . $this->geoLocation->getLongitude());
        }
        if($this->organizer) {
            $stream->addItem("ORGANIZER:" . $this->organizer);
        }
        if($this->priority) {
            $stream->addItem("PRIORITY:" . $this->priority);
        }
        if($this->status) {
            $stream->addItem("STATUS:" . $this->status);
        }
        if($this->transparency) {
            $stream->addItem("TRANSP:" . $this->transparency->value);
        }
        if($this->url) {
            $stream->addItem("URL:" . $this->url);
        }
        if($this->comment) {
            $stream->addItem("COMMENT:" . $this->comment);
        }
        if($this->contact) {
            $stream->addItem("CONTACT:" . $this->contact);
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
            if(str_starts_with($line, "DTSTART:") || str_starts_with($line, "DTSTART;")) {
                if(str_contains($line, "VALUE=DATE")) {
                    $this->dtStart = \DateTime::createFromFormat(Constants::D_FORMAT, substr($line, 19));
                }else{
                    $this->dtStart = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 8));
                }
                continue;
            }
            if(str_starts_with($line, "DTEND:") || str_starts_with($line, "DTEND;")) {
                if(str_contains($line, "VALUE=DATE")) {
                    $this->dtEnd = \DateTime::createFromFormat(Constants::D_FORMAT, substr($line, 17));
                }else{
                    $this->dtEnd = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 6));
                }
                continue;
            }
            if(str_starts_with($line, "CLASS:")) {
                $this->class = CalendarEventClassification::tryFrom(substr($line, 6));
                continue;
            }
            if(str_starts_with($line, "CREATED:")) {
                $this->created = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 8));
                continue;
            }
            if(str_starts_with($line, "LAST-MODIFIED:")) {
                $this->lastModified = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, 14));
                continue;
            }
            if(str_starts_with($line, "SUMMARY:")) {
                $this->summary = substr($line, 8);
                continue;
            }
            if(str_starts_with($line, "DESCRIPTION:")) {
                $this->description = substr($line, 12);
                continue;
            }
            if(str_starts_with($line, "LOCATION:")) {
                $this->location = $line;
                continue;
            }
            if(str_starts_with($line, "GEO:")) {
                $geo = explode(";", substr($line, 4));
                $this->geoLocation = new CalendarEventGeoLocation((float)$geo[0], (float)$geo[1]);
                continue;
            }
            if(str_starts_with($line, "ORGANIZER:")) {
                $this->organizer = substr($line, 10);
                continue;
            }
            if(str_starts_with($line, "PRIORITY:")) {
                $this->priority = (int)substr($line, 9);
                continue;
            }
            if(str_starts_with($line, "STATUS:")) {
                $this->status = substr($line, 7);
                continue;
            }
            if(str_starts_with($line, "TRANSP:")) {
                $this->transparency = CalendarEventTransparency::tryFrom(substr($line, 7));
                continue;
            }
            if(str_starts_with($line, "URL:")) {
                $this->url = substr($line, 4);
                continue;
            }
            if(str_starts_with($line, "COMMENT:")) {
                $this->comment = substr($line, 8);
                continue;
            }
            if(str_starts_with($line, "CONTACT:")) {
                $this->contact = substr($line, 8);
                continue;
            }
            $this->unknownImportLines[] = $line;
        }
        return $this;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;
        return $this;
    }

    public function getDtStamp(): DateTime
    {
        return $this->dtStamp;
    }

    public function setDtStamp(DateTime $dtStamp): self
    {
        $this->dtStamp = $dtStamp;
        return $this;
    }

    public function getDtStart(): DateTime
    {
        return $this->dtStart;
    }

    public function setDtStart(DateTime $dtStart): self
    {
        $this->dtStart = $dtStart;
        return $this;
    }

    public function getDtEnd(): DateTime
    {
        return $this->dtEnd;
    }

    public function setDtEnd(DateTime $dtEnd): self
    {
        $this->dtEnd = $dtEnd;
        return $this;
    }

    public function getClass(): ?CalendarEventClassification
    {
        return $this->class;
    }

    public function setClass(?CalendarEventClassification $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(?DateTime $created): self
    {
        $this->created = $created;
        return $this;
    }

    public function getLastModified(): ?DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(?DateTime $lastModified): self
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getGeoLocation(): ?CalendarEventGeoLocation
    {
        return $this->geoLocation;
    }

    public function setGeoLocation(?CalendarEventGeoLocation $geoLocation): self
    {
        $this->geoLocation = $geoLocation;
        return $this;
    }

    public function getOrganizer(): ?string
    {
        return $this->organizer;
    }

    public function setOrganizer(?string $organizer): self
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setPriority(?int $priority): self {
        if($priority != null && $priority < 0 || $priority > 9) {
            throw new InvalidArgumentException("Priority must be between 0 and 9");
        }
        $this->priority = $priority;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getTransparency(): ?CalendarEventTransparency
    {
        return $this->transparency;
    }

    public function setTransparency(?CalendarEventTransparency $transparency): self
    {
        $this->transparency = $transparency;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getContact(): ?string {
        return $this->contact;
    }

    public function setContact(?string $contact): self {
        $this->contact = $contact;
        return $this;
    }

    public function getUnknownImportLines(): array {
        return $this->unknownImportLines;
    }

}