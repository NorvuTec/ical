<?php

namespace Norvutec\ical\Model;

use Norvutec\ical\Constants;
use Norvutec\ical\Exception\IcalException;
use Norvutec\ical\Exception\UnexpectedCalendarDataException;
use Norvutec\ical\Stream\CalendarStream;
use Norvutec\ical\Stream\CalendarStreamReader;

class Calendar {

    /**
     * @var string The version of the calendar format
     */
    private string $version = "2.0";
    private string $prodId = "";
    private string $scale = "GREGORIAN";
    private string $method = "PUBLISH";

    private ?string $name = null;
    private ?string $description = null;
    private ?string $uid = null;
    private ?string $url = null;
    private ?string $timezone = null;
    private ?string $refreshInterval = null;
    private ?\DateTime $lastModified = null;
    private ?string $publishedTtl = null;
    private ?string $color = null;
    private ?string $image = null;
    private array $customHeaders = [];

    /**
     * @var array<CalendarEvent> The events in the calendar
     */
    private array $events = [];
    /**
     * @var array<string> lines of the import that are not known
     */
    private array $unknownImportLines = [];

    public function __construct() {
        $this->timezone = (new \DateTime())->getTimezone()->getName();
    }

    /**
     * Writes the calendar into the stream
     * @param CalendarStream $stream The stream to write to
     * @return void
     */
    public function write(CalendarStream $stream): void {
        $stream->addItem("BEGIN:VCALENDAR")
            ->addItem("VERSION:".$this->version)
            ->addItem("PRODID:".$this->prodId)
            ->addItem("CALSCALE:".$this->scale)
            ->addItem("METHOD:".$this->method);
        if($this->name) {
            $stream->addItem("NAME:".$this->name);
            $stream->addItem("X-WR-CALNAME:".$this->name);
        }
        if($this->description) {
            $stream->addItem("DESCRIPTION:".$this->description);
            $stream->addItem("X-WR-CALDESC:".$this->description);
        }
        if($this->uid) {
            $stream->addItem("UID:".$this->uid);
        }
        if($this->url) {
            $stream->addItem("URL:".$this->url);
            $stream->addItem("SOURCE:".$this->url);
        }
        if($this->timezone) {
            $stream->addItem("TIMEZONE:".$this->timezone);
            $stream->addItem("X-WR-TIMEZONE:".$this->timezone);
        }
        if($this->lastModified) {
            $stream->addItem("LAST-MODIFIED:".$this->lastModified->format(Constants::DT_FORMAT));
        }
        if($this->refreshInterval) {
            $stream->addItem("REFRESH-INTERVAL;VALUE=DURATION:".$this->refreshInterval);
        }
        if($this->publishedTtl) {
            $stream->addItem("X-PUBLISHED-TTL:".$this->publishedTtl);
        }
        if($this->color) {
            $stream->addItem("COLOR:".$this->color);
        }
        if($this->image) {
            $stream->addItem("IMAGE:".$this->image);
        }
        if(count($this->customHeaders) > 0) {
            foreach($this->customHeaders as $header) {
                $stream->addItem($header);
            }
        }
        foreach($this->events as $event) {
            $event->write($stream);
        }
    }

    /**
     * Reads the calendar from the stream
     * @param CalendarStreamReader $reader The reader to import the calendar
     * @throws IcalException if there is a problem while reading the calendar
     */
    public function read(CalendarStreamReader $reader): self {
        if(!$reader->hasNext()) return $this;
        $firstLine = $reader->readLine();
        if($firstLine !== "BEGIN:VCALENDAR") {
            throw new UnexpectedCalendarDataException("BEGIN:VCALENDAR", $firstLine);
        }
        $stillInHeader = true;
        while($reader->hasNext()) {
            $line = $reader->readLine();
            if($line === "END:VCALENDAR") {
                break;
            }
            if(str_starts_with("VERSION:", $line)) {
                $this->version = substr($line, 8);
                continue;
            }
            if(str_starts_with("PRODID:", $line)) {
                $this->prodId = substr($line, 7);
                continue;
            }
            if(str_starts_with("CALSCALE:", $line)) {
                $this->scale = substr($line, 9);
                continue;
            }
            if(str_starts_with("METHOD:", $line)) {
                $this->method = substr($line, 7);
                continue;
            }
            if(str_starts_with("NAME", $line) || str_starts_with("X-WR-CALNAME", $line)) {
                $this->name = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("DESCRIPTION", $line) || str_starts_with("X-WR-CALDESC", $line)) {
                $this->description = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("UID", $line)) {
                $this->uid = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("URL", $line) || str_starts_with("SOURCE", $line)) {
                $this->url = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("LAST-MODIFIED", $line)) {
                $this->lastModified = \DateTime::createFromFormat(Constants::DT_FORMAT, substr($line, strpos($line, ":")+1));
                continue;
            }
            if(str_starts_with("TIMEZONE", $line) || str_starts_with("X-WR-TIMEZONE", $line)) {
                $this->timezone = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("REFRESH-INTERVAL", $line)) {
                $this->refreshInterval = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("X-PUBLISHED-TTL", $line)) {
                $this->publishedTtl = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("COLOR", $line)) {
                $this->color = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("IMAGE", $line)) {
                $this->image = substr($line, strpos($line, ":")+1);
                continue;
            }
            if(str_starts_with("BEGIN:VEVENT", $line)) {
                $stillInHeader = false;
                $this->events[] = (new CalendarEvent())->read($reader->back());
                continue;
            }
            if($stillInHeader) {
                $this->customHeaders[] = $line;
            }else{
                $this->illegalImportLines[] = $line;
            }
        }
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getProdId(): string
    {
        return $this->prodId;
    }

    public function setProdId(string $prodId): self
    {
        $this->prodId = $prodId;
        return $this;
    }

    public function getScale(): string
    {
        return $this->scale;
    }

    public function setScale(string $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): self
    {
        $this->uid = $uid;
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

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getRefreshInterval(): ?string
    {
        return $this->refreshInterval;
    }

    public function setRefreshInterval(?string $refreshInterval): self
    {
        $this->refreshInterval = $refreshInterval;
        return $this;
    }

    public function getLastModified(): ?\DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTime $lastModified): self
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    public function getPublishedTtl(): ?string
    {
        return $this->publishedTtl;
    }

    public function setPublishedTtl(?string $publishedTtl): self
    {
        $this->publishedTtl = $publishedTtl;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getCustomHeaders(): array
    {
        return $this->customHeaders;
    }

    public function setCustomHeaders(array $customHeaders): self
    {
        $this->customHeaders = $customHeaders;
        return $this;
    }

    public function addCustomHeader(string $customHeader): self
    {
        $this->customHeaders[] = $customHeader;
        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function setEvents(array $events): self
    {
        $this->events = $events;
        return $this;
    }

    public function getUnknownImportLines(): array
    {
        return $this->unknownImportLines;
    }

}